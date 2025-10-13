<?php

namespace App\Services\Interfaces;

interface PackageServiceInterface
{
    public function updateOrderPayment($barcode);

    public function getContainer($officeId);

    public function validatePackage($package);

    public function createPackage($container, $package);
}
