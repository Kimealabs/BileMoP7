<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr-FR');

        $products = [
            ["model" => "V25 Pro", "brand" => "Vivo", "display" => "6.56-inch (1080x2376)", "front" => "32MP", "rear" => "64MP + 8MP + 2MP", "processor" => "Mediatek Dimensity 1300", "release" => "2022-08-18", "price" => "249.99"],
            ["model" => "iPhone 14", "brand" => "Apple", "display" => "6.06-inch (1170x2532)", "front" => "12MP", "rear" => "12MP + 12MP", "processor" => "Apple A15 Bionic", "release" => "2022-09-07", "price" => "890.00"],
            ["model" => "iPhone 14 Plus", "brand" => "Apple", "display" => "6.68-inch (1284x2778)", "front" => "12MP", "rear" => "12MP + 12MP", "processor" => "Apple A15 Bionic", "release" => "2022-08-18", "price" => "990.00"],
            ["model" => "C33", "brand" => "Realme", "display" => "6.50-inch (720x1600)", "front" => "5MP", "rear" => "50MP + 0.3MP", "processor" => "Unisoc T612", "release" => "2022-09-06", "price" => "249.99"],
            ["model" => "Redmi 11 Prime 5G", "brand" => "Xiaomi", "display" => "6.58-inch (2400x1080)", "front" => "8MP", "rear" => "50MP", "processor" => "Mediatek Dimensity 700", "release" => "2022-08-18", "price" => "449.99"],
            ["model" => "M5", "brand" => "Poco", "display" => "6.58-inch (2400x1080)", "front" => "8MP", "rear" => "50MP + 2MP + 2MP", "processor" => "Octa-core", "release" => "2022-09-05", "price" => "349.99"],
            ["model" => "Redmi note 11SE", "brand" => "Xiaomi", "display" => "6.43-inch (1800x2400)", "front" => "13MP", "rear" => "64MP + 8MP + 2MP + 2MP", "processor" => "Mediatek Helio G95", "release" => "2022-08-26", "price" => "449.99"],
            ["model" => "Moto G62 5G", "brand" => "Motorola", "display" => "6.50-inch (1080x2400)", "front" => "16MP", "rear" => "50MP + 8MP + 2MP", "processor" => "Qualcomm Snapdragon 695", "release" => "2022-08-11", "price" => "649.99"],
            ["model" => "Galaxy Z Fold 4", "brand" => "Samsung", "display" => "7.60-inch (2716x1812)", "front" => "10MP + 4MP", "rear" => "50MP + 12MP + 10MP", "processor" => "Qualcomm Snapdragon 8 + Gen 1", "release" => "2022-08-10", "price" => "749.99"],
            ["model" => "Galaxy A33", "brand" => "Samsung", "display" => "6.40-inch (1080x2400)", "front" => "5MP + 2MP", "rear" => "48MP", "processor" => "Octa-core", "release" => "2021-07-12", "price" => "349.90"],
            ["model" => "Y55", "brand" => "Vivo", "display" => "6.40-inch (1080x2400)", "front" => "2MP + 2MP", "rear" => "50MP", "processor" => "Octa-core", "release" => "2021-03-10", "price" => "249.90"],
            ["model" => "Redmi 10A", "brand" => "Xiaomi", "display" => "6.53-inch (720x1600)", "front" => "2MP", "rear" => "13MP", "processor" => "Cortex A53", "release" => "2020-02-20", "price" => "179.90"],
            ["model" => "Reno 8 5G", "brand" => "Oppo", "display" => "6.4-inch (1080x2400)", "front" => "64MP + 13MP + 8MP + 2MP", "rear" => "44MP + 2MP", "processor" => "Octa-core MediaTek Helio P95", "release" => "2019-08-17", "price" => "270.90"],
            ["model" => "Pixel 2 XL", "brand" => "Google", "display" => "6.0-inch (1440x2880)", "front" => "12MP", "rear" => "8MP", "processor" => "Octa-core Qualcomm Snapdragon 835 MSM899", "release" => "2017-11-15", "price" => "180.90"],

        ];

        $clients = [
            ["company" => "SFR", "email" => "admin@sfr.fr", "password" => "password"],
            ["company" => "Jeanne d'arc a FREE", "email" => "admin@free.fr", "password" => "password"],
            ["company" => "Le garage du mobile", "email" => "admin@lgdm.fr", "password" => "password"],
            ["company" => "Eurodiscount", "email" => "admin@eurodiscount.fr", "password" => "password"]
        ];

        foreach ($clients as $client) {
            $newClient = new Client();
            $newClient->setCompany($client["company"])
                ->setEmail($client["email"])
                ->setCreatedAt(new \DateTimeImmutable($faker->numberBetween(2018, 2021) . '-' . $faker->numberBetween(1, 12) . '-' . $faker->numberBetween(1, 28) . ' ' . $faker->numberBetween(1, 23) . ':00:00'))
                ->setPassword($this->hasher->hashPassword($newClient, $client["password"]))
                ->setRoles(["ROLE_USER"])
                ->setPhone($faker->phoneNumber());
            $manager->persist($newClient);

            for ($i = 0; $i < mt_rand(8, 20); $i++) {
                $newUser = new User();
                $newUser->setFirstname($faker->firstName())
                    ->setSecondname($faker->lastName())
                    ->setEmail($faker->email())
                    ->setAddress($faker->address())
                    ->setClient($newClient);
                $manager->persist($newUser);
            }
        }

        foreach ($products as $product) {
            $newProduct = new Product();
            $newProduct->setModel($product["model"])
                ->setBrand($product["brand"])
                ->setDisplay($product["display"])
                ->setFrontCamera($product["front"])
                ->setRearCamera($product["rear"])
                ->setProcessor($product["processor"])
                ->setReleaseDate(new \DateTimeImmutable($product["release"]))
                ->setPrice($product["price"])
                ->setCreatedAt(new \DateTimeImmutable('NOW'));
            $manager->persist($newProduct);
        }

        $manager->flush();
    }
}
