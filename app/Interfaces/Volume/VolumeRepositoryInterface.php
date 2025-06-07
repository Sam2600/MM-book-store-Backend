<?php

namespace App\Interfaces\Volume;

interface VolumeRepositoryInterface
{
   public function checkVolumeByIds(int|String $novelId, int|String $volumeId);

   public function getNovelTotalVolumeById(int|String $novelId);
}