<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Создаем таблицу ролей
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Добавляем базовые роли
        DB::table('roles')->insert([
            [
                'name' => 'user',
                'description' => 'Обычный пользователь системы',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'admin',
                'description' => 'Администратор системы',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Добавляем колонку role_id в таблицу users сразу как NOT NULL со значением по умолчанию
        Schema::table('users', function (Blueprint $table) {
            // Получаем ID роли user
            $userRoleId = DB::table('roles')->where('name', 'user')->value('id');
            
            $table->foreignId('role_id')->default($userRoleId)->after('password');
            $table->foreign('role_id')->references('id')->on('roles');
        });

        // Создаем индекс для оптимизации
        Schema::table('users', function (Blueprint $table) {
            $table->index('role_id');
        });

        // Назначаем роль admin первому пользователю (если есть)
        if ($adminRoleId = DB::table('roles')->where('name', 'admin')->value('id')) {
            DB::table('users')
                ->where('id', 1)
                ->update(['role_id' => $adminRoleId]);
        }
    }

    public function down()
    {
        // Удаляем внешний ключ и индекс
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropIndex(['role_id']);
            $table->dropColumn('role_id');
        });

        // Удаляем таблицу ролей
        Schema::dropIfExists('roles');
    }
}; 