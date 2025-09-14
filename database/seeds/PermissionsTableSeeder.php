<?php

use App\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $permissions = [[
            'id'         => '1',
            'title'      => 'user_management_access',
            'created_at' => '2019-04-15 19:14:42',
            'updated_at' => '2019-04-15 19:14:42',
        ],
            [
                'id'         => '2',
                'title'      => 'permission_create',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '3',
                'title'      => 'permission_edit',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '4',
                'title'      => 'permission_show',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '5',
                'title'      => 'permission_delete',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '6',
                'title'      => 'permission_access',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '7',
                'title'      => 'role_create',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '8',
                'title'      => 'role_edit',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '9',
                'title'      => 'role_show',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '10',
                'title'      => 'role_delete',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '11',
                'title'      => 'role_access',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '12',
                'title'      => 'user_create',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '13',
                'title'      => 'user_edit',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '14',
                'title'      => 'user_show',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '15',
                'title'      => 'user_delete',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '16',
                'title'      => 'user_access',
                'created_at' => '2019-04-15 19:14:42',
                'updated_at' => '2019-04-15 19:14:42',
            ],
            [
                'id'         => '22',
                'title'      => 'account_access',
                'created_at' => '2020-11-20 08:00:29',
                'updated_at' => '2021-03-26 03:00:15',
            ],
            [
                'id'         => '23',
                'title'      => 'account_create',
                'created_at' => '2021-03-26 03:00:15',
                'updated_at' => '2021-03-26 03:00:15',
            ],
            [
                'id'         => '24',
                'title'      => 'account_show',
                'created_at' => '2020-11-20 08:33:08',
                'updated_at' => '2021-03-26 03:00:15',
            ],
            [
                'id'         => '25',
                'title'      => 'account_edit',
                'created_at' => '2020-11-20 08:33:24',
                'updated_at' => '2021-03-26 03:00:15',
            ],
            [
                'id'         => '26',
                'title'      => 'account_delete',
                'created_at' => '2020-11-20 08:33:43',
                'updated_at' => '2021-03-26 03:00:15',
            ],
            [
                'id'         => '157',
                'title'      => 'customer_create',
                'created_at' => '2021-06-15 00:48:33',
                'updated_at' => '2021-06-15 00:48:33',
            ],
            [
                'id'         => '158',
                'title'      => 'categories_access',
                'created_at' => '2021-06-15 01:11:28',
                'updated_at' => '2021-06-15 01:11:28',
            ],
            [
                'id'         => '159',
                'title'      => 'categories_show',
                'created_at' => '2021-06-15 01:11:39',
                'updated_at' => '2021-06-15 01:11:39',
            ],
            [
                'id'         => '160',
                'title'      => 'categories_edit',
                'created_at' => '2021-06-15 01:11:50',
                'updated_at' => '2021-06-15 01:11:50',
            ],
            [
                'id'         => '161',
                'title'      => 'categories_create',
                'created_at' => '2021-06-15 01:12:00',
                'updated_at' => '2021-06-15 01:12:00',
            ],
            [
                'id'         => '162',
                'title'      => 'categories_delete',
                'created_at' => '2021-06-15 01:12:24',
                'updated_at' => '2021-06-15 01:12:24',
            ],
            [
                'id'         => '163',
                'title'      => 'customer_access',
                'created_at' => '2021-06-15 05:54:15',
                'updated_at' => '2021-06-15 05:54:15',
            ],
            [
                'id'         => '164',
                'title'      => 'customer_delete',
                'created_at' => '2021-06-15 05:54:24',
                'updated_at' => '2021-06-15 05:54:24',
            ],
            [
                'id'         => '165',
                'title'      => 'customer_edit',
                'created_at' => '2021-06-15 05:54:31',
                'updated_at' => '2021-06-15 05:54:31',
            ],
            [
                'id'         => '166',
                'title'      => 'dapertement_access',
                'created_at' => '2021-06-15 07:33:36',
                'updated_at' => '2021-06-15 07:33:36',
            ],
            [
                'id'         => '167',
                'title'      => 'dapertement_edit',
                'created_at' => '2021-06-15 07:33:47',
                'updated_at' => '2021-06-15 07:33:47',
            ],
            [
                'id'         => '168',
                'title'      => 'dapertement_create',
                'created_at' => '2021-06-15 07:33:59',
                'updated_at' => '2021-06-15 07:33:59',
            ],
            [
                'id'         => '169',
                'title'      => 'dapertement_delete',
                'created_at' => '2021-06-15 07:34:11',
                'updated_at' => '2021-06-15 07:34:11',
            ],
            [
                'id'         => '170',
                'title'      => 'staff_access',
                'created_at' => '2021-06-16 02:34:16',
                'updated_at' => '2021-06-16 02:34:16',
            ],
            [
                'id'         => '171',
                'title'      => 'staff_edit',
                'created_at' => '2021-06-16 02:34:30',
                'updated_at' => '2021-06-16 02:34:30',
            ],
            [
                'id'         => '172',
                'title'      => 'staff_delete',
                'created_at' => '2021-06-16 02:34:43',
                'updated_at' => '2021-06-16 02:34:43',
            ],
            [
                'id'         => '173',
                'title'      => 'staff_show',
                'created_at' => '2021-06-16 02:34:49',
                'updated_at' => '2021-06-16 02:34:49',
            ],
            [
                'id'         => '174',
                'title'      => 'staff_create',
                'created_at' => '2021-06-16 02:42:31',
                'updated_at' => '2021-06-16 02:42:31',
            ],
            [
                'id'         => '175',
                'title'      => 'ticket_create',
                'created_at' => '2021-06-16 06:08:07',
                'updated_at' => '2021-06-16 06:08:07',
            ],
            [
                'id'         => '176',
                'title'      => 'ticket_edit',
                'created_at' => '2021-06-16 06:08:34',
                'updated_at' => '2021-06-16 06:08:34',
            ],
            [
                'id'         => '177',
                'title'      => 'ticket_show',
                'created_at' => '2021-06-16 06:09:00',
                'updated_at' => '2021-06-16 06:09:00',
            ],
            [
                'id'         => '178',
                'title'      => 'ticket_access',
                'created_at' => '2021-06-16 06:09:13',
                'updated_at' => '2021-06-16 06:09:13',
            ],
            [
                'id'         => '179',
                'title'      => 'ticket_delete',
                'created_at' => '2021-06-16 06:09:39',
                'updated_at' => '2021-06-16 06:09:39',
            ],
            [
                'id'         => '180',
                'title'      => 'action_create',
                'created_at' => '2021-06-17 06:00:45',
                'updated_at' => '2021-06-17 06:00:45',
            ],
            [
                'id'         => '181',
                'title'      => 'action_edit',
                'created_at' => '2021-06-17 06:00:52',
                'updated_at' => '2021-06-17 06:00:52',
            ],
            [
                'id'         => '182',
                'title'      => 'action_show',
                'created_at' => '2021-06-17 06:01:00',
                'updated_at' => '2021-06-17 06:01:00',
            ],
            [
                'id'         => '183',
                'title'      => 'action_access',
                'created_at' => '2021-06-17 06:01:08',
                'updated_at' => '2021-06-17 06:01:08',
            ],
            [
                'id'         => '184',
                'title'      => 'action_delete',
                'created_at' => '2021-06-17 06:01:16',
                'updated_at' => '2021-06-17 06:01:16',
            ],
            [
                'id'         => '185',
                'title'      => 'action_staff_access',
                'created_at' => '2021-06-18 05:59:45',
                'updated_at' => '2021-06-18 05:59:45',
            ],
            [
                'id'         => '186',
                'title'      => 'action_staff_create',
                'created_at' => '2021-06-18 06:37:07',
                'updated_at' => '2021-06-18 06:37:07',
            ],
            [
                'id'         => '187',
                'title'      => 'action_staff_edit',
                'created_at' => '2021-06-18 06:37:18',
                'updated_at' => '2021-06-18 06:37:18',
            ],
            [
                'id'         => '188',
                'title'      => 'action_staff_delete',
                'created_at' => '2021-06-18 06:37:24',
                'updated_at' => '2021-06-18 06:37:24',
            ],
        ];

        Permission::insert($permissions);
    }
}
