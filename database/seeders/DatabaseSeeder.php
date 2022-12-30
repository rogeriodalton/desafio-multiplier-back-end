<?php

namespace Database\Seeders;

use Faker\Factory;
use App\Models\Menu;
use App\Models\User;
use App\Models\Board;
use App\Models\BoardLog;
use App\Models\OrderItem;
use App\Models\UserGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

define( "SPACE", '                                                    ');

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->menuSeeders(50);   //50
        $this->userSeeders(1000); //10000
        $this->orderSeeders(400000); //400000
    }

    private function orderSeeders(int $ii = 1)
    {
        $arraySql = (new Menu)->select('id')
                              ->get()
                              ->toArray();
        $menu = [];
        foreach ($arraySql as $key => $value) {
            array_push($menu, $value['id']);
        }

        $arraySql = (new Board())->select('id')
                                 ->get()
                                 ->toArray();

        $board = [];
        foreach ($arraySql as $key => $value) {
            array_push($board, $value['id']);
        }

        $arraySql = (new User())->join('user_groups', 'user_groups.user_id', 'users.id')
                                ->where('user_groups.group_id', 4)
                                ->select('users.id')
                                ->get()
                                ->toArray();
        $waiter = [];
        foreach ($arraySql as $key => $value) {
            array_push($waiter, $value['id']);
        }

        $arraySql = (new User())->join('user_groups', 'user_groups.user_id', 'users.id')
                                ->where('user_groups.group_id', 2)
                                ->select('users.id')
                                ->get()
                                ->toArray();
        $client = [];
        foreach ($arraySql as $key => $value) {
            array_push($client, $value['id']);
        }

        $faker = Factory::create('pt_BR');

        for ($i = 0; $i <= $ii; $i++) {
            $total = 0;
            $boardLog = new BoardLog;
            $boardLog->board_id = $faker->randomElement($board);
            $boardLog->waiter_id = $faker->randomElement($waiter);
            $boardLog->client_id = $faker->randomElement($client);
            $boardLog->state = 'fechado';
            $boardLog->save();

            foreach ($boardLog as $key => $value) {
                $ordersNum = $faker->numberBetween(1,4);
                $menuSelected = (new Menu)->where('id', $faker->randomElement($menu))
                                          ->first();

                for ($iii=0; $iii <= $ordersNum; $iii++) {
                    $orderItem = new OrderItem();
                    $orderItem->board_log_id = $boardLog->id;
                    $orderItem->menu_id = $menuSelected->id;
                    $orderItem->price = $menuSelected->price;
                    $orderItem->amount = $faker->numberBetween(1,3);
                    $orderItem->amount_to_pay = ($menuSelected->price * $orderItem->amount);
                    $orderItem->state = $faker->randomElement(['aguardando', 'preparando', 'pronto', 'entregue']);
                    $orderItem->save();
                    $total = $total + $orderItem->amount_to_pay;
                    $boardLog->total_order = $total;
                    $boardLog->save();
                    echo $i . ' - ' . $orderItem->id . SPACE . chr(13);
                }
            }
        }
    }

    private function menuSeeders(int $ii = 50)
    {
        $faker = Factory::create('pt_BR');

        for ($i = 0; $i <= $ii; $i++) {
            $menu = new Menu;
            $menu->name = $faker->words(3, true);
            $menu->description = $faker->words(20, true);
            $menu->price = $faker->randomFloat(2,1,50);
            $menu->save();
            echo $i . ' - ' . $menu->description . SPACE . chr(13);
        }
    }

    private function userSeeders(int $ii = 10)
    {
        $faker = Factory::create('pt_BR');

        for ($i = 0; $i <= $ii; $i++) {

            $user = new User;
            $userGroup = new UserGroup;
            $user->name  = $faker->unique->name;
            $user->email = $faker->unique->email;
            $user->cpf   = $faker->unique->cpf;
            $user->active = true;
            $user->password = Hash::make('password');
            $user->save();

            if ($i < 11)
                $userGroup->group_id = 3; //Cozinheiro
            elseif ($i < 21)
                $userGroup->group_id = 4; //Garçom
            else
                $userGroup->group_id = 2; //Cliente

            $userGroup->user_id = $user->id;
            $userGroup->save();

            echo $i . ' - ' . $user->name . SPACE . chr(13);

            unset($user);
            unset($userGroup);
        }
    }
}
