<?php

namespace App\Repositories\Volume;

use App\Models\Volume;
use App\Interfaces\Volume\VolumeRepositoryInterface;

class VolumeRepository implements VolumeRepositoryInterface
{
   public function checkVolumeByIds(int|String $novelId, int|String $volumeId)
   {
      return Volume::where("volume_number", $volumeId)
               ->where("novel_id", $novelId)
               ->firstOrFail();
   }
   
   public function getNovelTotalVolumeById(int|String $novelId)
   {
      return Volume::where("volume_number", $novelId)->count();
   }
}