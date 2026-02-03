<?php

namespace Database\Seeders;

use App\Models\TopologyTemplate;
use Illuminate\Database\Seeder;

class TopologyTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // FTTH Standard Template
        TopologyTemplate::updateOrCreate(
            ['name' => 'FTTH Standard'],
            [
                'description' => 'Template standar untuk layanan FTTH (Fiber to the Home)',
                'is_system' => true,
                'topology_data' => [
                    'nodes' => [
                        [
                            'id' => 'backbone-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 0],
                            'data' => [
                                'label' => 'ISP Backbone',
                                'deviceType' => 'backbone',
                                'color' => '#8b5cf6',
                            ],
                        ],
                        [
                            'id' => 'router-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 100],
                            'data' => [
                                'label' => 'Main Router',
                                'deviceType' => 'router',
                                'color' => '#3b82f6',
                            ],
                        ],
                        [
                            'id' => 'olt-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 200],
                            'data' => [
                                'label' => 'OLT',
                                'deviceType' => 'olt',
                                'color' => '#f97316',
                            ],
                        ],
                        [
                            'id' => 'odp-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 300],
                            'data' => [
                                'label' => 'ODP',
                                'deviceType' => 'odp',
                                'color' => '#22c55e',
                            ],
                        ],
                        [
                            'id' => 'ont-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 400],
                            'data' => [
                                'label' => 'ONT',
                                'deviceType' => 'ont',
                                'color' => '#14b8a6',
                            ],
                        ],
                        [
                            'id' => 'customer-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 500],
                            'data' => [
                                'label' => 'Pelanggan',
                                'deviceType' => 'customer',
                                'color' => '#64748b',
                            ],
                        ],
                    ],
                    'edges' => [
                        ['id' => 'e-backbone-router', 'source' => 'backbone-1', 'target' => 'router-1'],
                        ['id' => 'e-router-olt', 'source' => 'router-1', 'target' => 'olt-1'],
                        ['id' => 'e-olt-odp', 'source' => 'olt-1', 'target' => 'odp-1'],
                        ['id' => 'e-odp-ont', 'source' => 'odp-1', 'target' => 'ont-1'],
                        ['id' => 'e-ont-customer', 'source' => 'ont-1', 'target' => 'customer-1'],
                    ],
                ],
            ]
        );

        // Point-to-Point Template
        TopologyTemplate::updateOrCreate(
            ['name' => 'Point to Point (PTP)'],
            [
                'description' => 'Template untuk koneksi Point-to-Point langsung',
                'is_system' => true,
                'topology_data' => [
                    'nodes' => [
                        [
                            'id' => 'backbone-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 0],
                            'data' => [
                                'label' => 'ISP Backbone',
                                'deviceType' => 'backbone',
                                'color' => '#8b5cf6',
                            ],
                        ],
                        [
                            'id' => 'router-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 100],
                            'data' => [
                                'label' => 'Router',
                                'deviceType' => 'router',
                                'color' => '#3b82f6',
                            ],
                        ],
                        [
                            'id' => 'switch-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 200],
                            'data' => [
                                'label' => 'Switch',
                                'deviceType' => 'switch',
                                'color' => '#6366f1',
                            ],
                        ],
                        [
                            'id' => 'customer-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 300],
                            'data' => [
                                'label' => 'Pelanggan',
                                'deviceType' => 'customer',
                                'color' => '#64748b',
                            ],
                        ],
                    ],
                    'edges' => [
                        ['id' => 'e-backbone-router', 'source' => 'backbone-1', 'target' => 'router-1'],
                        ['id' => 'e-router-switch', 'source' => 'router-1', 'target' => 'switch-1'],
                        ['id' => 'e-switch-customer', 'source' => 'switch-1', 'target' => 'customer-1'],
                    ],
                ],
            ]
        );

        // Radio Wireless Template  
        TopologyTemplate::updateOrCreate(
            ['name' => 'Wireless Radio'],
            [
                'description' => 'Template untuk koneksi wireless/radio',
                'is_system' => true,
                'topology_data' => [
                    'nodes' => [
                        [
                            'id' => 'backbone-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 0],
                            'data' => [
                                'label' => 'ISP Backbone',
                                'deviceType' => 'backbone',
                                'color' => '#8b5cf6',
                            ],
                        ],
                        [
                            'id' => 'router-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 250, 'y' => 100],
                            'data' => [
                                'label' => 'Router',
                                'deviceType' => 'router',
                                'color' => '#3b82f6',
                            ],
                        ],
                        [
                            'id' => 'ap-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 100, 'y' => 200],
                            'data' => [
                                'label' => 'AP Station',
                                'deviceType' => 'custom',
                                'color' => '#ec4899',
                            ],
                        ],
                        [
                            'id' => 'cpe-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 400, 'y' => 200],
                            'data' => [
                                'label' => 'CPE Client',
                                'deviceType' => 'custom',
                                'color' => '#f59e0b',
                            ],
                        ],
                        [
                            'id' => 'customer-1',
                            'type' => 'networkDevice',
                            'position' => ['x' => 400, 'y' => 300],
                            'data' => [
                                'label' => 'Pelanggan',
                                'deviceType' => 'customer',
                                'color' => '#64748b',
                            ],
                        ],
                    ],
                    'edges' => [
                        ['id' => 'e-backbone-router', 'source' => 'backbone-1', 'target' => 'router-1'],
                        ['id' => 'e-router-ap', 'source' => 'router-1', 'target' => 'ap-1'],
                        ['id' => 'e-ap-cpe', 'source' => 'ap-1', 'sourceHandle' => 'right', 'target' => 'cpe-1', 'targetHandle' => 'left'],
                        ['id' => 'e-cpe-customer', 'source' => 'cpe-1', 'target' => 'customer-1'],
                    ],
                ],
            ]
        );
    }
}
