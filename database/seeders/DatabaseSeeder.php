<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Department;
use App\Models\EquipmentBrand;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use App\Models\ParkingLimit;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Venue;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = User::factory()->create([
            'name' => 'Super',
            'surname' => 'Admin',
            'email' => 'admin@adservis.com',
        ]);
        $admin = User::factory()->create([
            'name' => 'Sr. Marisa',
            'surname' => 'Tumbali, SPC',
            'email' => 'sr_marisa@spup.edu.ph',
        ]);
        $genservice = User::factory()->create([
            'name' => 'Michelle',
            'surname' => 'Lim',
            'email' => 'mlim@spup.edu.ph',
        ]);
        $maintenance = User::factory()->create([
            'name' => 'Maintenance',
            'surname' => 'Test',
            'email' => 'maintenance@email.com',
        ]);
        $contractor = User::factory()->create([
            'name' => 'Contractor',
            'surname' => 'Test',
            'email' => 'contractor@email.com',
        ]);
        $finance = User::factory()->create([
            'name' => 'VP',
            'surname' => 'Finance',
            'email' => 'finance@email.com',
        ]);
        $facilitator = User::factory()->create([
            'name' => 'Facilitator',
            'surname' => 'Test',
            'email' => 'facilitator@email.com',
        ]);
        $sitehead = User::factory()->create([
            'name' => 'Dr. Marifel Grace',
            'surname' => 'Kummer',
            'email' => 'mkummer@spup.edu.ph',
        ]);
        $snahshead = User::factory()->create([
            'name' => 'Dr. Anunciacion',
            'surname' => 'Talosig',
            'email' => 'atalosig@spup.edu.ph',
        ]);
        $sbahmhead = User::factory()->create([
            'name' => 'Dr. Charito',
            'surname' => 'Guillermo',
            'email' => 'cguillermo@spup.edu.ph',
        ]);
        $sastehead = User::factory()->create([
            'name' => 'Dr. Evelyn Elizabeth',
            'surname' => 'Pacquing',
            'email' => 'epacquing@spup.edu.ph',
        ]);

        // create permissions
        Permission::create(['name' => 'Manage Users']);
        Permission::create(['name' => 'Manage Job Orders']);
        Permission::create(['name' => 'Manage Venue Bookings']);
        Permission::create(['name' => 'Manage Sticker Applications']);
        Permission::create(['name' => 'Recommend Job Orders']);
        Permission::create(['name' => 'Be Assigned to Job Orders']);
        Permission::create(['name' => 'Note Venue Bookings']);
        Permission::create(['name' => 'Approve Venue Bookings as Finance']);
        Permission::create(['name' => 'Be In-charge of Venues']);
        Permission::create(['name' => 'Post Job Orders']);
        Permission::create(['name' => 'Book Venues']);
        Permission::create(['name' => 'Apply for Sticker']);


        Role::create(['name' => 'Super Admin'])
            ->givePermissionTo('Manage Users');

        Role::create(['name' => 'Admin'])
            ->givePermissionTo(Permission::all());

        Role::create(['name' => 'PP/GS Head'])
            ->givePermissionTo(['Manage Users', 'Recommend Job Orders', 'Apply for Sticker', 'Apply for Sticker']);

        Role::create(['name' => 'Unit Head'])
            ->givePermissionTo(['Post Job Orders', 'Note Venue Bookings', 'Apply for Sticker', 'Book Venues']);

        Role::create(['name' => 'Maintenance'])
            ->givePermissionTo(['Be Assigned to Job Orders', 'Apply for Sticker']);

        Role::create(['name' => 'Contractor'])
            ->givePermissionTo(['Be Assigned to Job Orders', 'Apply for Sticker']);

        Role::create(['name' => 'Employee'])
            ->givePermissionTo(['Book Venues', 'Apply for Sticker']);

        Role::create(['name' => 'Student'])
            ->givePermissionTo(['Book Venues', 'Apply for Sticker']);

        Role::create(['name' => 'VP Finance'])
            ->givePermissionTo(['Approve Venue Bookings as Finance', 'Apply for Sticker']);

        Role::create(['name' => 'Facilitator'])
            ->givePermissionTo(['Be In-charge of Venues', 'Apply for Sticker']);


        // foreach (Role::all() as $role) {
        //     User::factory(10)->create()->each(function ($user) {
        //         // Randomly assign either 'Student' or 'Employee' role to each user
        //         $role = ['Student', 'Employee'][rand(0, 1)];
        //         $user->assignRole($role);
        //     });
        // }

        // foreach (Role::all() as $role) {
        //     User::factory(2)->create()->each(function ($user) {
        //         // Randomly assign either 'Student' or 'Employee' role to each user
        //         $role = ['Maintenance', 'Contractor'][rand(0, 1)];
        //         $user->assignRole($role);
        //     });
        // }

        $this->seedVenues();

        $superAdmin->assignRole('Super Admin');
        $admin->assignRole('Admin');
        $genservice->assignRole('PP/GS Head');
        $maintenance->assignRole('Maintenance');
        $contractor->assignRole('Contractor');
        $finance->assignRole('VP Finance');
        $facilitator->assignRole('Facilitator');
        $sitehead->assignRole('Unit Head');
        $snahshead->assignRole('Unit Head');
        $sbahmhead->assignRole('Unit Head');
        $sastehead->assignRole('Unit Head');


        //Departments Seeder for Parking Sticker Application
        $departments = [
            ['name' => 'Graduate School'],
            ['name' => 'College'],
            ['name' => 'Basic Education'],
            ['name' => 'Employee']
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        //Parking Limits Seeder for Parking Sticker Application
        $parkingLimits = [
            ['department_id' => 1, 'vehicle_category' => '4 Wheels', 'limit' => 50],
            ['department_id' => 1, 'vehicle_category' => '2 Wheels', 'limit' => 100],
            ['department_id' => 2, 'vehicle_category' => '4 Wheels', 'limit' => 30],
            ['department_id' => 2, 'vehicle_category' => '2 Wheels', 'limit' => 80],
            ['department_id' => 3, 'vehicle_category' => '4 Wheels', 'limit' => 40],
            ['department_id' => 3, 'vehicle_category' => '2 Wheels', 'limit' => 60],
            ['department_id' => 4, 'vehicle_category' => '4 Wheels', 'limit' => 50],
            ['department_id' => 4, 'vehicle_category' => '2 Wheels', 'limit' => 100],
        ];

        foreach ($parkingLimits as $limit) {
            ParkingLimit::create($limit);
        }

        //Vehicles Seeder for Parking Sticker Application
        $vehicles = [
            ['type' => 'Sedan', 'sticker_cost' => 1000.00, 'category' => '4 Wheels'],
            ['type' => 'SUV', 'sticker_cost' => 1000.00, 'category' => '4 Wheels'],
            ['type' => 'Motorcycle', 'sticker_cost' => 500.00, 'category' => '2 Wheels'],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }


        Unit::create(['name' => 'Al Fresco', 'code' => 'AF']);
        Unit::create(['name' => 'Alumni, External Relations and Advocacies', 'code' => 'AERA']);
        Unit::create(['name' => 'BAO Accounts Payable', 'code' => 'BAOAF']);
        Unit::create(['name' => 'BAO Assessment', 'code' => 'BAOA']);
        Unit::create(['name' => 'BAO Cashier', 'code' => 'BAOC']);
        Unit::create(['name' => 'BAO General Accounting', 'code' => 'BAOGA']);
        Unit::create(['name' => 'BAO Payroll', 'code' => 'BAOP']);
        Unit::create(['name' => 'BAO Purchasing Office', 'code' => 'BAOPO']);
        Unit::create(['name' => 'Basic Education Unit', 'code' => 'BEU']);
        Unit::create(['name' => 'BEU Preschool', 'code' => 'BEUP']);
        Unit::create(['name' => 'Board Room Kitchen', 'code' => 'BRK']);
        Unit::create(['name' => 'Boutique', 'code' => 'BTQ']);
        Unit::create(['name' => 'Christian Formation Office', 'code' => 'CF']);
        Unit::create(['name' => 'Clinic', 'code' => 'CLINIC']);
        Unit::create(['name' => 'College Guidance Office', 'code' => 'CGO']);
        Unit::create(['name' => 'Community Extension Services Office', 'code' => 'CESO']);
        Unit::create(['name' => 'Copy Center', 'code' => 'CC']);
        Unit::create(['name' => 'Data Center', 'code' => 'DC']);
        Unit::create(['name' => 'Eco Center', 'code' => 'ECO']);
        Unit::create(['name' => 'Food Services', 'code' => 'FS']);
        Unit::create(['name' => 'Grade School', 'code' => 'GS']);
        Unit::create(['name' => 'Graduate School Office', 'code' => 'GSO']);
        Unit::create(['name' => 'GS Library', 'code' => 'GSLIB']);
        Unit::create(['name' => 'High School Library', 'code' => 'HSLIB']);
        Unit::create(['name' => 'Human Resource', 'code' => 'HRM']);
        Unit::create(['name' => 'ICT', 'code' => 'ICT']);
        Unit::create(['name' => 'Junior High School', 'code' => 'JHS']);
        Unit::create(['name' => 'KIRN', 'code' => 'KIRN']);
        Unit::create(['name' => 'MM Hall', 'code' => 'MMH']);
        Unit::create(['name' => 'Physical Plant and General Services Office', 'code' => 'PPGSO']);
        Unit::create(['name' => 'Powerhouse', 'code' => 'PWRHS']);
        Unit::create(['name' => 'President\'s Office', 'code' => 'PO']);
        Unit::create(['name' => 'PSG Office', 'code' => 'PSG']);
        Unit::create(['name' => 'Quality Assurance Center', 'code' => 'QAC']);
        Unit::create(['name' => 'Quality Assurance Office', 'code' => 'QAO']);
        Unit::create(['name' => 'Records and Releasing', 'code' => 'RR']);
        Unit::create(['name' => 'Registrar\'s Office', 'code' => 'RO']);
        Unit::create(['name' => 'Research and Publications Office', 'code' => 'RPO']);
        Unit::create(['name' => 'Research Director', 'code' => 'RD']);
        Unit::create(['name' => 'Review Center', 'code' => 'RC']);
        Unit::create(['name' => 'SASTE', 'code' => 'SASTE', 'unit_head' => '11']);
        Unit::create(['name' => 'SBAHM', 'code' => 'SBAHM','unit_head' => '10']);
        Unit::create(['name' => 'School of Medicine Office', 'code' => 'SOM']);
        Unit::create(['name' => 'Science Laboratory Office (3rd Flr.)', 'code' => 'SLO']);
        Unit::create(['name' => 'Science Laboratory FLC (5th Flr.)', 'code' => 'SLFLC']);
        Unit::create(['name' => 'Science Multi Media Room', 'code' => 'SMMR']);
        Unit::create(['name' => 'Science Research Laboratory (2nd Flr.)', 'code' => 'SRL']);
        Unit::create(['name' => 'Senior High School', 'code' => 'SHS']);
        Unit::create(['name' => 'Senior High School Faculty and Library', 'code' => 'SHSO']);
        Unit::create(['name' => 'Senior High School Library (FLC 5th Flr.)', 'code' => 'SHSLIB']);
        Unit::create(['name' => 'Senior High School Science Laboratory', 'code' => 'SHSLAB']);
        Unit::create(['name' => 'Senior High School Guidance Office', 'code' => 'SHSGO']);
        Unit::create(['name' => 'SITE', 'code' => 'SITE', 'unit_head' => '8']);
        Unit::create(['name' => 'SNAHS', 'code' => 'SNAHS', 'unit_head' => '9']);
        Unit::create(['name' => 'SPC', 'code' => 'SPC']);
        Unit::create(['name' => 'Student Affairs Office', 'code' => 'SAO']);
        Unit::create(['name' => 'University Registrar', 'code' => 'UR']);
        Unit::create(['name' => 'Vice-President for Academics', 'code' => 'VPACAD']);
        Unit::create(['name' => 'Vice-President for Administration', 'code' => 'VPAD', 'unit_head' => $admin->id]);

        EquipmentCategory::create(['name' => 'Computer and Peripheral Equipment', 'code' => '0001']);
        EquipmentCategory::create(['name' => 'Electric Fan', 'code' => '0002']);
        EquipmentCategory::create(['name' => 'Airconditioning Unit', 'code' => '0003']);
        EquipmentCategory::create(['name' => 'Audio and Video Equipment', 'code' => '0004']);
        EquipmentCategory::create(['name' => 'Medical Equipment and Supplies', 'code' => '0005']);
        EquipmentCategory::create(['name' => 'Service Industry Equipment', 'code' => '0006']);
        EquipmentCategory::create(['name' => 'Chairs', 'code' => '0007']);
        EquipmentCategory::create(['name' => 'Kitchen Hardware', 'code' => '0008']);
        EquipmentCategory::create(['name' => 'Plumbing Hardware', 'code' => '0009']);
        EquipmentCategory::create(['name' => 'Cabinets', 'code' => '0010']);
        EquipmentCategory::create(['name' => 'Tables', 'code' => '0011']);
        EquipmentCategory::create(['name' => 'Construction and Safety Equipment', 'code' => '0012']);

        EquipmentBrand::create(['name' => 'Acer', 'equipment_category_id' => '1', 'code' => '01']);
        EquipmentBrand::create(['name' => 'HP', 'equipment_category_id' => '1', 'code' => '02']);
        EquipmentBrand::create(['name' => 'Samsung', 'equipment_category_id' => '1', 'code' => '03']);
        EquipmentBrand::create(['name' => 'Lenovo', 'equipment_category_id' => '1', 'code' => '04']);
        EquipmentBrand::create(['name' => 'Dell', 'equipment_category_id' => '1', 'code' => '05']);
        EquipmentBrand::create(['name' => 'A4Tech', 'equipment_category_id' => '1', 'code' => '06']);
        EquipmentBrand::create(['name' => 'Secure', 'equipment_category_id' => '1', 'code' => '07']);
        EquipmentBrand::create(['name' => 'APC', 'equipment_category_id' => '1', 'code' => '08']);
        EquipmentBrand::create(['name' => 'Toshiba', 'equipment_category_id' => '1', 'code' => '09']);
        EquipmentBrand::create(['name' => 'Seagate', 'equipment_category_id' => '1', 'code' => '10']);
        EquipmentBrand::create(['name' => 'Socomec', 'equipment_category_id' => '1', 'code' => '11']);
        EquipmentBrand::create(['name' => 'APC', 'equipment_category_id' => '1', 'code' => '12']);
        EquipmentBrand::create(['name' => 'Brother', 'equipment_category_id' => '1', 'code' => '13']);
        EquipmentBrand::create(['name' => 'Canon', 'equipment_category_id' => '1', 'code' => '14']);
        EquipmentBrand::create(['name' => 'Epson', 'equipment_category_id' => '1', 'code' => '15']);
        EquipmentBrand::create(['name' => 'Seagate', 'equipment_category_id' => '1', 'code' => '16']);
        EquipmentBrand::create(['name' => 'Lexmark', 'equipment_category_id' => '1', 'code' => '17']);
        EquipmentBrand::create(['name' => 'Kodak', 'equipment_category_id' => '1', 'code' => '18']);
        EquipmentBrand::create(['name' => 'Linksys', 'equipment_category_id' => '1', 'code' => '19']);
        EquipmentBrand::create(['name' => 'OAC', 'equipment_category_id' => '1', 'code' => '20']);
        EquipmentBrand::create(['name' => 'Rionin', 'equipment_category_id' => '1', 'code' => '21']);
        EquipmentBrand::create(['name' => 'Intel', 'equipment_category_id' => '1', 'code' => '22']);
        EquipmentBrand::create(['name' => 'No/Unknown Brand', 'equipment_category_id' => '1', 'code' => '23']);
        EquipmentBrand::create(['name' => 'Apple', 'equipment_category_id' => '1', 'code' => '24']);

        EquipmentBrand::create(['name' => 'Standards', 'equipment_category_id' => '2', 'code' => '01']);
        EquipmentBrand::create(['name' => 'Hanabishi', 'equipment_category_id' => '2', 'code' => '02']);
        EquipmentBrand::create(['name' => 'Dowell', 'equipment_category_id' => '2', 'code' => '03']);
        EquipmentBrand::create(['name' => 'Asahi', 'equipment_category_id' => '2', 'code' => '04']);
        EquipmentBrand::create(['name' => 'Camel', 'equipment_category_id' => '2', 'code' => '05']);
        EquipmentBrand::create(['name' => 'Panasonic', 'equipment_category_id' => '2', 'code' => '06']);
        EquipmentBrand::create(['name' => 'KDK', 'equipment_category_id' => '2', 'code' => '07']);
        EquipmentBrand::create(['name' => 'No/Unknown Brand', 'equipment_category_id' => '2', 'code' => '08']);
        EquipmentBrand::create(['name' => '3d Sporty', 'equipment_category_id' => '2', 'code' => '09']);

        EquipmentBrand::create(['name' => 'Carrier', 'equipment_category_id' => '3', 'code' => '01']);
        EquipmentBrand::create(['name' => 'Samsung', 'equipment_category_id' => '3', 'code' => '02']);
        EquipmentBrand::create(['name' => 'Panasonic', 'equipment_category_id' => '3', 'code' => '03']);
        EquipmentBrand::create(['name' => 'LG', 'equipment_category_id' => '3', 'code' => '04']);
        EquipmentBrand::create(['name' => 'American Standards', 'equipment_category_id' => '3', 'code' => '05']);
        EquipmentBrand::create(['name' => 'Toshiba', 'equipment_category_id' => '3', 'code' => '06']);
        EquipmentBrand::create(['name' => 'Condura', 'equipment_category_id' => '3', 'code' => '07']);

        EquipmentBrand::create(['name' => 'Sharp', 'equipment_category_id' => '4', 'code' => '01']);
        EquipmentBrand::create(['name' => 'LG', 'equipment_category_id' => '4', 'code' => '02']);
        EquipmentBrand::create(['name' => 'Samsung', 'equipment_category_id' => '4', 'code' => '03']);
        EquipmentBrand::create(['name' => 'Pioneer', 'equipment_category_id' => '4', 'code' => '04']);
        EquipmentBrand::create(['name' => 'Panasonic', 'equipment_category_id' => '4', 'code' => '05']);
        EquipmentBrand::create(['name' => 'Sony', 'equipment_category_id' => '4', 'code' => '06']);
        EquipmentBrand::create(['name' => 'Haier', 'equipment_category_id' => '4', 'code' => '07']);
        EquipmentBrand::create(['name' => 'JVC', 'equipment_category_id' => '4', 'code' => '08']);
        EquipmentBrand::create(['name' => 'Sanyo', 'equipment_category_id' => '4', 'code' => '09']);
        EquipmentBrand::create(['name' => 'Philips', 'equipment_category_id' => '4', 'code' => '10']);
        EquipmentBrand::create(['name' => 'Rionin', 'equipment_category_id' => '4', 'code' => '11']);
        EquipmentBrand::create(['name' => 'Shure', 'equipment_category_id' => '4', 'code' => '12']);
        EquipmentBrand::create(['name' => 'No/Unknown Brand', 'equipment_category_id' => '4', 'code' => '13']);

        // EquipmentBrand::create(['name' => 'Sharp', 'equipment_category_id' => '5', 'code' => '01']);
        // EquipmentBrand::create(['name' => 'LG', 'equipment_category_id' => '5', 'code' => '02']);
        // EquipmentBrand::create(['name' => 'Samsung', 'equipment_category_id' => '5', 'code' => '03']);
        // EquipmentBrand::create(['name' => 'Pioneer', 'equipment_category_id' => '5', 'code' => '04']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '5', 'code' => '05']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '5', 'code' => '06']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '5', 'code' => '07']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '5', 'code' => '08']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '5', 'code' => '09']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '5', 'code' => '10']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '5', 'code' => '11']);

        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '01']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '02']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '03']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '04']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '05']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '06']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '07']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '08']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '09']);

        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '01']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '02']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '03']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '04']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '05']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '06']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '07']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '08']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '09']);

        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '01']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '02']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '03']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '04']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '05']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '06']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '07']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '08']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '09']);

        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '01']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '02']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '03']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '04']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '05']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '06']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '07']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '08']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '09']);

        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '01']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '02']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '03']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '04']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '05']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '06']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '07']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '08']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '09']);

        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '01']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '02']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '03']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '04']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '05']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '06']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '07']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '08']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '09']);

        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '01']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '02']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '03']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '04']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '05']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '06']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '07']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '08']);
        // EquipmentBrand::create(['name' => '', 'equipment_category_id' => '', 'code' => '09']);

        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);
        // EquipmentType::create(['name' => '', 'equipment_category_id' => '1', 'code' => '']);

    }

    private function seedVenues()
    {
        $venues = [
            [
                'name' => 'Student Center',
                'capacity' => 100,
                'facilitator' => 7,
                'description' => 'A vibrant hub for student activities,  This covered gymnasium is a multi-purpose facility which provides a spacious area for indoor sports activities, athletic events, assemblies, and social gatherings.',
            ],
            [
                'name' => 'Student Center - BEU',
                'capacity' => 100,
                'facilitator' => 7,
                'description' => 'Dedicated to the younger students, this covered gymnasium caters to the physical education needs of the Basic Education Unit. Imagine a bright and energetic space where children can develop their motor skills and enjoy the camaraderie of team sports.',
            ],
            [
                'name' => 'Science Multi-Media',
                'capacity' => 100,
                'facilitator' => 7,
                'description' => 'Immerse yourself in a world of discovery at the Science Multi-Media Center. This advanced learning space utilizes multimedia tools to bring scientific concepts to life.  Expect interactive exhibits, engaging presentations, and cutting-edge technology to spark curiosity and ignite a passion for science.',
            ],
            [
                'name' => 'Mother Madeleine Hall',
                'capacity' => 100,
                'facilitator' => 7,
                'description' => 'A place for reflection and inspiration: Named after a potentially revered figure (founder, religious leader, etc.), this hall could serve as a space for prayer, meditation, or spiritual gatherings. It might offer a peaceful atmosphere with religious iconography or calming design elements.',
            ],
            // Add more venues as needed
        ];

        $imagePaths = [
            'Student Center' => [
                'public/images/SPUP AdServIS/Student Center.jpg',
                'public/images/SPUP AdServIS/Student Center 2.jpg',
            ],
            'Student Center - BEU' => [
                'public/images/SPUP AdServIS/Student Center - BEU.jpg',
                'public/images/SPUP AdServIS/Student Center - BEU 2.jpg',
            ],
            'Science Multi-Media' => [
                'public/images/SPUP AdServIS/Science Multi-Media.jpg',
                'public/images/SPUP AdServIS/Science Multi-Media 2.jpg',
            ],
            'Mother Madeleine Hall' => [
                'public/images/SPUP AdServIS/Mother Madeleine Hall.jpg',
                'public/images/SPUP AdServIS/Mother Madeleine Hall 2.jpg',
            ],
            // Add more venues and their image paths as needed
        ];

        foreach ($venues as $venueData) {
            $venue = Venue::create($venueData);

            if (isset($imagePaths[$venueData['name']])) {
                foreach ($imagePaths[$venueData['name']] as $imagePath) {
                    $venue->copyMedia($imagePath)->toMediaCollection($venue->name . '_images');
                }
            }
        }
    }
}
