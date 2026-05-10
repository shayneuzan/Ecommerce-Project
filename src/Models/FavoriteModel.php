<?php

declare(strict_types=1);

namespace App\Models;
use RedBeanPHP\R;

class FavoriteModel{

    public function isFavorite(int $userId, int $packageId) : bool{

        $favorite = R::findOne('favorite', 'user_id = ? AND package_id = ?', [$userId, $packageId]);

        return $favorite !== null;
    }

    public function add(int $userId, int $packageId) : void{

        $favorite = R::dispense('favorite');
        $favorite->user_id = $userId;
        $favorite->package_id = $packageId;
        $favorite->created_at = date('Y-m-d H:i:s');
        R::store($favorite);
    }

    public function remove(int $userId, int $packageId) : void{

        $favorite = R::findOne('favorite', 'user_id = ? AND package_id = ?', [$userId, $packageId]);

        if($favorite){
            R::trash($favorite);
        }
    }

    public function toggle(int $userId, int $packageId) : bool{
        if($this->isFavorite($userId, $packageId)){
            $this->remove($userId, $packageId);
            return false; 
        } else {
            $this->add($userId, $packageId);
            return true;
        }
    }

    public function getUserFavorites(int $userId) : array{
        return R::getAll('SELECT p.*, d.city,d.country
            FROM favorite f
            JOIN package p ON f.package_id = p.id
            JOIN destination d ON p.destination_id = d.id
            WHERE f.user_id = ?
            ', [$userId]);
    }

    public function getUserFavoriteId(int $userId) : array{
        $rows = R::find('favorite', 'user_id = ?', [$userId]);
        if (!$rows) return [];
        return array_column($rows, 'package_id');
    }



} 