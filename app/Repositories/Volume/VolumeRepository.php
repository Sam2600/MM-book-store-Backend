<?php

namespace App\Repositories\Volume;

use App\Models\Volume;
use App\Interfaces\Volume\VolumeRepositoryInterface;

class VolumeRepository implements VolumeRepositoryInterface
{
   public function checkVolumeByIds(int|String $novelId, int|String $volumeId)
   {
      return Volume::select('id')
               ->where("volume_number", $volumeId)
               ->where("novel_id", $novelId)
               ->first();
   }
   
   public function getNovelTotalVolumeById(int|String $novelId)
   {
      return Volume::where("volume_number", $novelId)->count();
   }
}