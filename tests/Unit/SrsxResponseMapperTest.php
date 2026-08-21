<?php

namespace Tests\Unit;

use App\DomainRegistrars\Srsx\SrsxResponseMapper;
use PHPUnit\Framework\TestCase;

class SrsxResponseMapperTest extends TestCase
{
    public function test_map_xml_success_1000(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>1000</resultCode><resultMsg>Domain Available</resultMsg></result><resultData/></epp>';
        $result = $mapper->mapXml($xml);
        $this->assertTrue($result['success']);
        $this->assertSame('1000', $result['code']);
        $this->assertSame('Domain Available', $result['message']);
    }

    public function test_map_xml_1001_is_not_generic_success(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>1001</resultCode><resultMsg>Command Failed</resultMsg></result></epp>';
        $result = $mapper->mapXml($xml);
        $this->assertFalse($result['success']); // P1: 1001 tidak sukses global, harus per endpoint
        $this->assertSame('1001', $result['code']);
    }

    public function test_map_register_waiting_doc_is_success(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>1001</resultCode><resultMsg>Domain is still waiting for the complete document</resultMsg></result></epp>';
        $result = $mapper->mapXml($xml);
        $mapped = $mapper->mapRegister($result);
        $this->assertTrue($mapped['success']);
        $this->assertTrue($mapped['pending_doc'] ?? false);
    }

    public function test_map_register_failed_is_not_success(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>1001</resultCode><resultMsg>Create Domain Failed</resultMsg></result></epp>';
        $result = $mapper->mapXml($xml);
        $mapped = $mapper->mapRegister($result);
        $this->assertFalse($mapped['success']);
    }

    public function test_map_test_connection_1001_is_success(): void
    {
        $mapper = new SrsxResponseMapper;
        $result = ['success' => false, 'code' => '1001', 'message' => 'Command Failed', 'data' => []];
        $mapped = $mapper->mapTestConnection($result);
        $this->assertTrue($mapped['success']); // untuk test koneksi, 1001 tetap dianggap koneksi OK
    }

    public function test_map_xml_failed(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>2001</resultCode><resultMsg>Auth Failed</resultMsg></result></epp>';
        $result = $mapper->mapXml($xml);
        $this->assertFalse($result['success']);
        $this->assertSame('2001', $result['code']);
    }

    public function test_map_xml_invalid(): void
    {
        $mapper = new SrsxResponseMapper;
        $result = $mapper->mapXml('not xml');
        $this->assertFalse($result['success']);
        $this->assertSame('invalid_xml', $result['code']);
    }

    public function test_map_availability_available(): void
    {
        $mapper = new SrsxResponseMapper;
        $result = ['success' => true, 'code' => '1000', 'message' => 'Domain Available', 'data' => []];
        $mapped = $mapper->mapAvailability($result);
        $this->assertTrue($mapped['available']);
    }

    public function test_map_availability_taken(): void
    {
        $mapper = new SrsxResponseMapper;
        $result = ['success' => true, 'code' => '1001', 'message' => 'Command Failed', 'data' => []];
        $mapped = $mapper->mapAvailability($result);
        $this->assertFalse($mapped['available']);
    }

    public function test_redact_removes_secrets(): void
    {
        $mapper = new SrsxResponseMapper;
        $payload = ['username' => 'user', 'password' => 'secret', 'domain' => 'example.com', 'api_key' => 'key'];
        $redacted = $mapper->redact($payload);
        $this->assertSame('***', $redacted['password']);
        $this->assertSame('***', $redacted['api_key']);
        $this->assertSame('example.com', $redacted['domain']);
    }

    public function test_map_domain_info_success(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result><resultData><domain>example.com</domain><enddate>2025-12-31</enddate><status>active</status></resultData></epp>';
        $result = $mapper->mapXml($xml);
        $mapped = $mapper->mapDomainInfo($result);
        $this->assertTrue($mapped['success']);
        $this->assertSame('example.com', $mapped['data']['domain']);
    }

    public function test_map_domain_info_normalizes_dates_nameservers_and_contacts(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result><resultData><domainid>35</domainid><domain>example.com</domain><startdate>2024-01-10</startdate><enddate>2025-01-10</enddate><contact_registrant>11</contact_registrant><contact_admin>12</contact_admin><ns1>ns1.example.com</ns1><ns2>ns2.example.com</ns2><status>active</status></resultData></epp>';

        $mapped = $mapper->mapDomainInfo($mapper->mapXml($xml));

        $this->assertSame('35', $mapped['data']['provider_domain_id']);
        $this->assertSame('2024-01-10', $mapped['data']['registered_at']);
        $this->assertSame('2025-01-10', $mapped['data']['expires_at']);
        $this->assertSame(['ns1.example.com', 'ns2.example.com'], $mapped['data']['nameservers']);
        $this->assertSame(['registrant' => '11', 'admin' => '12'], $mapped['data']['contact_ids']);
    }

    public function test_map_contact_info_returns_provider_contact_data(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result><resultData><contactid>11</contactid><fname>Jane</fname><email>jane@example.com</email></resultData></epp>';

        $mapped = $mapper->mapContactInfo($mapper->mapXml($xml));

        $this->assertTrue($mapped['success']);
        $this->assertSame('Jane', $mapped['data']['fname']);
    }

    public function test_map_epp_success_returns_epp(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result><resultData><epp>AbCd1234</epp></resultData></epp>';
        $result = $mapper->mapXml($xml);
        $mapped = $mapper->mapEpp($result);
        $this->assertTrue($mapped['success']);
        $this->assertSame('AbCd1234', $mapped['epp']);
    }

    public function test_map_epp_1001_fails(): void
    {
        $mapper = new SrsxResponseMapper;
        $result = ['success' => false, 'code' => '1001', 'message' => 'Command Failed', 'data' => []];
        $mapped = $mapper->mapEpp($result);
        $this->assertFalse($mapped['success']);
    }

    public function test_map_epp_empty_data_fails(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result><resultData></resultData></epp>';
        $result = $mapper->mapXml($xml);
        $mapped = $mapper->mapEpp($result);
        $this->assertFalse($mapped['success']);
        $this->assertSame('empty_epp', $mapped['code']);
    }

    public function test_map_nameservers_1000_success(): void
    {
        $mapper = new SrsxResponseMapper;
        $result = ['success' => true, 'code' => '1000', 'message' => 'Nameserver Updated', 'data' => []];
        $mapped = $mapper->mapNameservers($result);
        $this->assertTrue($mapped['success']);
    }

    public function test_map_nameservers_non_1000_fails(): void
    {
        $mapper = new SrsxResponseMapper;
        $result = ['success' => false, 'code' => '1001', 'message' => 'Command Failed', 'data' => []];
        $mapped = $mapper->mapNameservers($result);
        $this->assertFalse($mapped['success']);
    }

    public function test_map_dns_info_normalizes_records(): void
    {
        $mapper = new SrsxResponseMapper;
        $xml = '<?xml version="1.0" encoding="UTF-8"?><epp><result><resultCode>1000</resultCode><resultMsg>OK</resultMsg></result><resultData><dns0><line>1</line><type>A</type><record>@</record><destination>1.2.3.4</destination><ttl>3600</ttl></dns0><dns1><line>2</line><type>CNAME</type><record>www</record><destination>example.com</destination><ttl>3600</ttl></dns1><domain_ns1>ns1.example.com</domain_ns1></resultData></epp>';
        $result = $mapper->mapXml($xml);
        $mapped = $mapper->mapDnsInfo($result);
        $this->assertTrue($mapped['success']);
        $this->assertCount(2, $mapped['data']['records']);
        $this->assertSame('A', $mapped['data']['records'][0]['type']);
        $this->assertContains('ns1.example.com', $mapped['data']['nameservers']);
    }

    public function test_map_dns_edit_1000_success(): void
    {
        $mapper = new SrsxResponseMapper;
        $result = ['success' => true, 'code' => '1000', 'message' => 'DNS Updated', 'data' => []];
        $mapped = $mapper->mapDnsEdit($result);
        $this->assertTrue($mapped['success']);
    }
}
