<?php
namespace App\Traits;

use App\Entity\Font;
use App\Entity\Format;
use App\Entity\Image;
use App\Repository\FontRepository;
use App\Repository\FormatRepository;
use App\Repository\ImageRepository;
use App\Utility\FileUtility;
use App\Utility\GeneralUtility;
use App\Utility\RequestUtility;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Trait ControllerTrait
 */
trait ControllerTrait {
    protected function getProjectDirectory(): string {
        return $this->getParameter('kernel.project_dir');
    }

    protected function getCachePath(): string {
        $projectDirectory = $this->getParameter('kernel.cache_dir');
        $path = $projectDirectory . '/images';
        $filesystem = new Filesystem();
        $filesystem->mkdir($path);
        return $path;
    }

}
