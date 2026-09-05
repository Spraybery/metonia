<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\MaterialMovement;
use App\Models\Supervisor;
use App\Models\Tool;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use App\Models\VehicleStageHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Seed Core RBAC Users (5 Specific System Profiles)
        $users = [
            [
                'name' => 'Eng. Martin Kariuki',
                'username' => 'admin',
                'email' => 'admin@metonia.co.ke',
                'password' => Hash::make('password'),
                'role' => 'Admin',
            ],
            [
                'name' => 'Grace Nduta',
                'username' => 'manager',
                'email' => 'manager@metonia.co.ke',
                'password' => Hash::make('password'),
                'role' => 'Manager',
            ],
            [
                'name' => 'Eng. Patrick Kamau',
                'username' => 'supervisor',
                'email' => 'supervisor@metonia.co.ke',
                'password' => Hash::make('password'),
                'role' => 'General Supervisor',
            ],
            [
                'name' => 'David Omondi',
                'username' => 'shopkeeper',
                'email' => 'shopkeeper@metonia.co.ke',
                'password' => Hash::make('password'),
                'role' => 'Shopkeeper',
            ],
            [
                'name' => 'Alice Wambui',
                'username' => 'accountant',
                'email' => 'accountant@metonia.co.ke',
                'password' => Hash::make('password'),
                'role' => 'Accountant',
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['username' => $u['username']], $u);
        }

        // 2. Seed Lead Supervisors
        $supervisors = [
            [
                'name' => 'Eng. Hassan Mohamed',
                'title' => 'Intake & Diagnostics Specialist',
                'stage' => '1. Intake & Diagnosis',
                'phone' => '+254 722 300 202',
                'shift' => 'Day Shift (07:00 - 16:00)',
            ],
            [
                'name' => 'Eng. Peter Kimani',
                'title' => 'Structural & Frame Lead',
                'stage' => '2. Structural & Frame',
                'phone' => '+254 711 200 101',
                'shift' => 'Day Shift (07:00 - 16:00)',
            ],
            [
                'name' => 'Eng. John Otieno',
                'title' => 'Powertrain & Transmission Lead',
                'stage' => '3. Powertrain & Mechanical',
                'phone' => '+254 733 400 303',
                'shift' => 'Full Day Lead',
            ],
            [
                'name' => 'Eng. Catherine Mwangi',
                'title' => 'Auto-Electrical Specialist',
                'stage' => '4. Electrical & Harness',
                'phone' => '+254 720 500 404',
                'shift' => 'Day Shift (07:00 - 16:00)',
            ],
            [
                'name' => 'Eng. Kevin Mutua',
                'title' => 'Bodywork & Spray Paint Lead',
                'stage' => '5. Bodywork & Spray Paint',
                'phone' => '+254 715 600 505',
                'shift' => 'Evening Shift (15:00 - 23:00)',
            ],
            [
                'name' => 'Eng. Brian Kipchirchir',
                'title' => 'Interior & Glass Fit Specialist',
                'stage' => '6. Interior & Glass Fit',
                'phone' => '+254 718 700 606',
                'shift' => 'Day Shift (07:00 - 16:00)',
            ],
            [
                'name' => 'Eng. Patrick Kamau',
                'title' => 'Plant Operations Director',
                'stage' => 'All Stages',
                'phone' => '+254 722 800 707',
                'shift' => 'Plant Operations',
            ],
        ];

        foreach ($supervisors as $s) {
            Supervisor::create($s);
        }

        // 3. Seed Store Materials
        $materialsData = [
            [
                'name' => 'Heavy Duty Structural Steel Beam 100x50mm',
                'category' => 'Metals',
                'unit' => 'Pieces',
                'qty' => 42.00,
                'low_stock' => 10.00,
                'unit_cost' => 14500.00,
                'supplier' => 'Apex Steel Kenya Ltd',
            ],
            [
                'name' => 'Aluminium Tread Plate Sheet 4x8ft 3mm',
                'category' => 'Aluminium',
                'unit' => 'Pieces',
                'qty' => 28.00,
                'low_stock' => 8.00,
                'unit_cost' => 22000.00,
                'supplier' => 'Bamburi Metals',
            ],
            [
                'name' => 'Grade 8.8 High-Tensile Chassis Bolts M16x80',
                'category' => 'Bolts & Fasteners',
                'unit' => 'Pieces',
                'qty' => 450.00,
                'low_stock' => 100.00,
                'unit_cost' => 350.00,
                'supplier' => 'Fastener Supplies Ltd',
            ],
            [
                'name' => 'Hydraulic Fluid ISO VG 46 Heavy Duty',
                'category' => 'Consumables',
                'unit' => 'Liters',
                'qty' => 320.00,
                'low_stock' => 50.00,
                'unit_cost' => 680.00,
                'supplier' => 'TotalEnergies Kenya',
            ],
            [
                'name' => 'Automotive 2K Polyurethane Primer Grey',
                'category' => 'Consumables',
                'unit' => 'Liters',
                'qty' => 3.00, // LOW STOCK ALERT!
                'low_stock' => 15.00,
                'unit_cost' => 2400.00,
                'supplier' => 'Crown Paints Kenya',
            ],
            [
                'name' => 'Pneumatic Reinforced Air Hose 1/2 Inch',
                'category' => 'Rubbers',
                'unit' => 'Rolls',
                'qty' => 12.00,
                'low_stock' => 5.00,
                'unit_cost' => 8500.00,
                'supplier' => 'Nairobi Rubber Works',
            ],
            [
                'name' => 'Heavy Duty Commercial Mudflaps 600x400mm',
                'category' => 'Rubbers',
                'unit' => 'Pieces',
                'qty' => 2.00, // LOW STOCK ALERT!
                'low_stock' => 10.00,
                'unit_cost' => 1800.00,
                'supplier' => 'Nairobi Rubber Works',
            ],
            [
                'name' => 'Reflective Chevron Warning Tape 50mm Red/White',
                'category' => 'Reflecting & Safety',
                'unit' => 'Rolls',
                'qty' => 18.00,
                'low_stock' => 6.00,
                'unit_cost' => 3200.00,
                'supplier' => '3M Kenya Safety',
            ],
            [
                'name' => 'Woven Fibreglass Mat 450gsm Heavy Body',
                'category' => 'Fibreglass',
                'unit' => 'Rolls',
                'qty' => 1.00, // LOW STOCK ALERT!
                'low_stock' => 4.00,
                'unit_cost' => 16500.00,
                'supplier' => 'Kenpoly Resins',
            ],
            [
                'name' => 'Heavy Vehicle Wire Harness 7-Core 1.5mm',
                'category' => 'Consumables',
                'unit' => 'Rolls',
                'qty' => 25.00,
                'low_stock' => 8.00,
                'unit_cost' => 12000.00,
                'supplier' => 'Metsec Cables',
            ],
            [
                'name' => 'Brake Lining Rivets 6x15mm Brass',
                'category' => 'Bolts & Fasteners',
                'unit' => 'Pieces',
                'qty' => 850.00,
                'low_stock' => 200.00,
                'unit_cost' => 45.00,
                'supplier' => 'Fastener Supplies Ltd',
            ],
        ];

        $createdMaterials = [];
        foreach ($materialsData as $m) {
            $mat = Material::create($m);
            $createdMaterials[$mat->name] = $mat;

            MaterialMovement::create([
                'material_id' => $mat->id,
                'material_name' => $mat->name,
                'type' => 'in',
                'qty' => $mat->qty + 5.0,
                'unit' => $mat->unit,
                'date' => Carbon::now()->subDays(15)->toDateString(),
                'person' => 'David Omondi',
                'note' => 'Quarterly store delivery consignment.',
            ]);
        }

        // 4. Seed Vehicles (Active & Stuck)
        // Vehicle 1: STUCK >= 10 Days in Stage 2
        $v1 = Vehicle::create([
            'plate' => 'MET-2026-8849102',
            'make' => 'Metonia',
            'model' => 'Titan 4x4 Heavy Hauler',
            'year' => '2026',
            'customer_name' => 'Trans-East Logistics Ltd',
            'customer_phone' => '+254 711 988 777',
            'stage' => '2. Structural & Frame',
            'assigned_to' => 'Eng. Peter Kimani',
            'intake_date' => Carbon::now()->subDays(24),
            'notes' => 'Custom reinforced chassis required for northern corridor heavy transport.',
            'checklist_done' => 1,
            'checklist_total' => 3,
            'labor_cost' => 185000.00,
            'invoice_total' => 3400000.00,
        ]);

        VehicleStageHistory::create([
            'vehicle_id' => $v1->id,
            'stage' => '1. Intake & Diagnosis',
            'transitioned_at' => Carbon::now()->subDays(24),
        ]);
        VehicleStageHistory::create([
            'vehicle_id' => $v1->id,
            'stage' => '2. Structural & Frame',
            'transitioned_at' => Carbon::now()->subDays(14), // Stuck for 14 days!
        ]);

        // Issue parts to v1
        $steel = $createdMaterials['Heavy Duty Structural Steel Beam 100x50mm'];
        $bolts = $createdMaterials['Grade 8.8 High-Tensile Chassis Bolts M16x80'];

        VehiclePart::create([
            'vehicle_id' => $v1->id,
            'material_id' => $steel->id,
            'material_name' => $steel->name,
            'qty' => 4.00,
            'unit_cost' => $steel->unit_cost,
            'cost' => 4.00 * $steel->unit_cost,
            'issued_at' => Carbon::now()->subDays(13),
        ]);

        VehiclePart::create([
            'vehicle_id' => $v1->id,
            'material_id' => $bolts->id,
            'material_name' => $bolts->name,
            'qty' => 50.00,
            'unit_cost' => $bolts->unit_cost,
            'cost' => 50.00 * $bolts->unit_cost,
            'issued_at' => Carbon::now()->subDays(12),
        ]);

        // Vehicle 2: STUCK >= 10 Days in Stage 3
        $v2 = Vehicle::create([
            'plate' => 'MET-2026-7731209',
            'make' => 'Metonia',
            'model' => 'Rhino Tipper 6x4',
            'year' => '2026',
            'customer_name' => 'Mombasa Cement Haulage',
            'customer_phone' => '+254 722 444 333',
            'stage' => '3. Powertrain & Mechanical',
            'assigned_to' => 'Eng. John Otieno',
            'intake_date' => Carbon::now()->subDays(18),
            'notes' => 'Transmission mount upgrade and dual hydraulic ram fitting.',
            'checklist_done' => 2,
            'checklist_total' => 3,
            'labor_cost' => 220000.00,
            'invoice_total' => 4800000.00,
        ]);

        VehicleStageHistory::create([
            'vehicle_id' => $v2->id,
            'stage' => '1. Intake & Diagnosis',
            'transitioned_at' => Carbon::now()->subDays(18),
        ]);
        VehicleStageHistory::create([
            'vehicle_id' => $v2->id,
            'stage' => '2. Structural & Frame',
            'transitioned_at' => Carbon::now()->subDays(15),
        ]);
        VehicleStageHistory::create([
            'vehicle_id' => $v2->id,
            'stage' => '3. Powertrain & Mechanical',
            'transitioned_at' => Carbon::now()->subDays(11), // Stuck for 11 days!
        ]);

        // Vehicle 3: Active in Stage 4 (Not stuck, 3 days)
        $v3 = Vehicle::create([
            'plate' => 'KDA-491M',
            'make' => 'Metonia',
            'model' => 'Bus Cruiser 62-Seater',
            'year' => '2025',
            'customer_name' => 'Guardian Coast Coaches',
            'customer_phone' => '+254 733 111 222',
            'stage' => '4. Electrical & Harness',
            'assigned_to' => 'Eng. Catherine Mwangi',
            'intake_date' => Carbon::now()->subDays(9),
            'notes' => 'Complete multiplex electrical harness with USB passenger ports.',
            'checklist_done' => 2,
            'checklist_total' => 3,
            'labor_cost' => 140000.00,
            'invoice_total' => 5800000.00,
        ]);

        VehicleStageHistory::create([
            'vehicle_id' => $v3->id,
            'stage' => '1. Intake & Diagnosis',
            'transitioned_at' => Carbon::now()->subDays(9),
        ]);
        VehicleStageHistory::create([
            'vehicle_id' => $v3->id,
            'stage' => '4. Electrical & Harness',
            'transitioned_at' => Carbon::now()->subDays(3),
        ]);

        // Vehicle 4: Active in Stage 5 (Not stuck, 4 days)
        $v4 = Vehicle::create([
            'plate' => 'KDF-890B',
            'make' => 'Metonia',
            'model' => 'Prime Mover Heavy Cargo',
            'year' => '2026',
            'customer_name' => 'Siginon Aviation Cargo',
            'customer_phone' => '+254 720 999 888',
            'stage' => '5. Bodywork & Spray Paint',
            'assigned_to' => 'Eng. Kevin Mutua',
            'intake_date' => Carbon::now()->subDays(16),
            'notes' => 'Dual polyurethane paint coat with customer reflective livery.',
            'checklist_done' => 1,
            'checklist_total' => 3,
            'labor_cost' => 280000.00,
            'invoice_total' => 6200000.00,
        ]);

        VehicleStageHistory::create([
            'vehicle_id' => $v4->id,
            'stage' => '5. Bodywork & Spray Paint',
            'transitioned_at' => Carbon::now()->subDays(4),
        ]);

        // Vehicle 5: Active in Stage 7 (Not stuck, 2 days)
        $v5 = Vehicle::create([
            'plate' => 'KBZ-112T',
            'make' => 'Metonia',
            'model' => 'Safari Special 4x4 Tourer',
            'year' => '2025',
            'customer_name' => 'Pollmans Tours & Safaris',
            'customer_phone' => '+254 725 667 889',
            'stage' => '7. Quality & Road Test',
            'assigned_to' => 'Eng. Patrick Kamau',
            'intake_date' => Carbon::now()->subDays(12),
            'notes' => 'Pop-up roof canopy and long-range suspension calibration.',
            'checklist_done' => 3,
            'checklist_total' => 3,
            'labor_cost' => 95000.00,
            'invoice_total' => 2400000.00,
        ]);

        VehicleStageHistory::create([
            'vehicle_id' => $v5->id,
            'stage' => '7. Quality & Road Test',
            'transitioned_at' => Carbon::now()->subDays(2),
        ]);

        // Vehicle 6: Completed & Dispatched (This Month MTD for Margin Calculation)
        $v6 = Vehicle::create([
            'plate' => 'KCP-556Q',
            'make' => 'Metonia',
            'model' => 'Tanker Semi-Trailer 35,000L',
            'year' => '2026',
            'customer_name' => 'Dalbit Petroleum Ltd',
            'customer_phone' => '+254 719 333 444',
            'stage' => '8. Completed & Dispatched',
            'assigned_to' => 'Eng. Patrick Kamau',
            'intake_date' => Carbon::now()->subDays(28),
            'notes' => 'Aluminium compartment pressure test certified and dispatched.',
            'checklist_done' => 3,
            'checklist_total' => 3,
            'labor_cost' => 340000.00,
            'invoice_total' => 7800000.00,
            'completed_at' => Carbon::now()->subDays(3),
        ]);

        VehicleStageHistory::create([
            'vehicle_id' => $v6->id,
            'stage' => '8. Completed & Dispatched',
            'transitioned_at' => Carbon::now()->subDays(3),
        ]);

        // Vehicle 7: Completed & Dispatched (This Month MTD)
        $v7 = Vehicle::create([
            'plate' => 'KDB-782P',
            'make' => 'Metonia',
            'model' => 'Low-Bed Heavy Transporter',
            'year' => '2026',
            'customer_name' => 'Kenya National Highways Authority',
            'customer_phone' => '+254 722 000 111',
            'stage' => '8. Completed & Dispatched',
            'assigned_to' => 'Eng. Peter Kimani',
            'intake_date' => Carbon::now()->subDays(25),
            'notes' => 'Multi-axle hydraulic steering commissioning completed.',
            'checklist_done' => 3,
            'checklist_total' => 3,
            'labor_cost' => 290000.00,
            'invoice_total' => 5500000.00,
            'completed_at' => Carbon::now()->subDays(1),
        ]);

        VehicleStageHistory::create([
            'vehicle_id' => $v7->id,
            'stage' => '8. Completed & Dispatched',
            'transitioned_at' => Carbon::now()->subDays(1),
        ]);

        // 5. Seed Tools & Equipment Register
        $tools = [
            [
                'asset_tag' => 'TL-PNEU-108',
                'name' => 'Ingersoll Rand 1/2-Inch Air Impact Wrench',
                'category' => 'Pneumatic Tools',
                'brand' => 'Ingersoll Rand',
                'location' => 'Tool Crib A - Bay 1',
                'status' => 'Available',
                'assigned_to' => null,
                'next_calibration' => Carbon::now()->addMonths(3),
            ],
            [
                'asset_tag' => 'TL-TORQ-042',
                'name' => 'Norbar Digital Torque Wrench 60-300Nm',
                'category' => 'Torque & Calibration Gauges',
                'brand' => 'Norbar',
                'location' => 'Bay 3 Mechanical',
                'status' => 'Checked Out',
                'assigned_to' => 'Eng. John Otieno',
                'next_calibration' => Carbon::now()->subDays(5), // OVERDUE CALIBRATION!
            ],
            [
                'asset_tag' => 'TL-WELD-201',
                'name' => 'Lincoln Electric Invertec 400A Heavy MIG Welder',
                'category' => 'Welding & Plasma Cutters',
                'brand' => 'Lincoln Electric',
                'location' => 'Bay 2 Structural',
                'status' => 'Available',
                'assigned_to' => null,
                'next_calibration' => Carbon::now()->addMonths(2),
            ],
            [
                'asset_tag' => 'TL-SCAN-015',
                'name' => 'Bosch KTS Truck Commercial Diagnostic Scanner',
                'category' => 'Diagnostic Scanners',
                'brand' => 'Bosch',
                'location' => 'Diagnostic Lab',
                'status' => 'Checked Out',
                'assigned_to' => 'Eng. Hassan Mohamed',
                'next_calibration' => Carbon::now()->addDays(8), // UPCOMING!
            ],
            [
                'asset_tag' => 'TL-LIFT-003',
                'name' => 'Rotary Lift 30-Ton Heavy Duty Column Lift System',
                'category' => 'Lifts & Hydraulics',
                'brand' => 'Rotary',
                'location' => 'Bay 4 Undercarriage',
                'status' => 'Available',
                'assigned_to' => null,
                'next_calibration' => Carbon::now()->addMonths(4),
            ],
            [
                'asset_tag' => 'TL-PLAS-008',
                'name' => 'Hypertherm Powermax 85 Plasma Cutter',
                'category' => 'Welding & Plasma Cutters',
                'brand' => 'Hypertherm',
                'location' => 'Maintenance Crib',
                'status' => 'In Maintenance',
                'assigned_to' => null,
                'next_calibration' => Carbon::now()->subDays(12), // OVERDUE CALIBRATION!
            ],
        ];

        foreach ($tools as $t) {
            Tool::create($t);
        }

        // 6. Seed Recent Activity Logs
        $activities = [
            ['actor' => 'Eng. Martin Kariuki', 'message' => 'System initialized and Nairobi Plant #1 calibrated.'],
            ['actor' => 'David Omondi', 'message' => 'Restocked 320 Liters of Hydraulic Fluid ISO VG 46.'],
            ['actor' => 'Grace Nduta', 'message' => 'Job card created for MET-2026-8849102 (Metonia Titan 4x4 Heavy Hauler).'],
            ['actor' => 'Eng. Peter Kimani', 'message' => 'MET-2026-8849102 moved to 2. Structural & Frame.'],
            ['actor' => 'David Omondi', 'message' => 'Issued 4.00 Pieces of Heavy Duty Structural Steel Beam 100x50mm to MET-2026-8849102.'],
            ['actor' => 'Grace Nduta', 'message' => 'Job card created for MET-2026-7731209 (Metonia Rhino Tipper 6x4).'],
            ['actor' => 'Eng. John Otieno', 'message' => 'MET-2026-7731209 moved to 3. Powertrain & Mechanical.'],
            ['actor' => 'Eng. Patrick Kamau', 'message' => 'KCP-556Q completed road test and successfully dispatched to Dalbit Petroleum Ltd.'],
            ['actor' => 'Eng. Patrick Kamau', 'message' => 'KDB-782P transitioned to 8. Completed & Dispatched.'],
            ['actor' => 'Alice Wambui', 'message' => 'Verified MTD financial invoice reconciliation for completed builds.'],
        ];

        foreach ($activities as $a) {
            ActivityLog::create([
                'actor' => $a['actor'],
                'message' => $a['message'],
                'created_at' => Carbon::now()->subHours(rand(1, 48)),
            ]);
        }
    }
}
