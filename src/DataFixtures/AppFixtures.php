<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
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
        $faker = Factory::create('fr_FR');

        // =============================================
        // 1. CATÉGORIES
        // =============================================
        $categoryData = [
            'Figurines'   => 'Figurines et statues de collection',
            'Manga'       => 'Mangas, light novels et BD japonaises',
            'Goodies'     => 'Porte-clés, mugs, badges et accessoires',
            'Posters'     => 'Posters et affiches décoratives',
            'Plushies'    => 'Peluches et coussins',
            'Cosplay'     => 'Costumes et accessoires de cosplay',
            'Video Games' => 'Jeux vidéo japonais et rétro',
            'TCG Cards'   => 'Cartes à collectionner (Pokémon, Yu-Gi-Oh...)',
            'Decoration'  => 'Lampes LED, tableaux et déco anime',
            'Clothing'    => 'T-shirts, sweats et vêtements anime',
        ];

        $categories = [];
        foreach ($categoryData as $name => $desc) {
            $cat = new Category();
            $cat->setName($name);
            $cat->setDescription($desc);
            $manager->persist($cat);
            $categories[] = $cat;
        }

        // =============================================
        // 2. UTILISATEURS
        // =============================================

        // --- Admin ---
        $admin = new User();
        $admin->setEmail('admin@otaku-shop.fr');
        $admin->setPseudo('AdminOtaku');
        $admin->setLastName('Admin');
        $admin->setFirstName('Super');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $admin->setTheme('dark');
        $admin->setLocale('fr');
        $manager->persist($admin);

        // --- Vendeurs ---
        $sellers = [];
        $sellerData = [
            ['naruto_shop', 'Uzumaki',  'Naruto',  'naruto@otaku-shop.fr'],
            ['luffy_store', 'Monkey',   'Luffy',   'luffy@otaku-shop.fr'],
            ['gojo_merch',  'Gojo',     'Satoru',  'gojo@otaku-shop.fr'],
        ];

        foreach ($sellerData as [$pseudo, $lastName, $firstName, $email]) {
            $u = new User();
            $u->setEmail($email);
            $u->setPseudo($pseudo);
            $u->setLastName($lastName);
            $u->setFirstName($firstName);
            $u->setRoles(['ROLE_USER']);
            $u->setPassword($this->hasher->hashPassword($u, 'user123'));
            $u->setTheme('light');
            $u->setLocale('fr');
            $manager->persist($u);
            $sellers[] = $u;
        }

        // --- Acheteurs ---
        $buyers = [];
        for ($i = 0; $i < 5; $i++) {
            $u = new User();
            $u->setEmail($faker->unique()->email());
            $u->setPseudo($faker->unique()->userName());
            $u->setLastName($faker->lastName());
            $u->setFirstName($faker->firstName());
            $u->setRoles(['ROLE_USER']);
            $u->setPassword($this->hasher->hashPassword($u, 'user123'));
            $u->setTheme($faker->randomElement(['light', 'dark']));
            $u->setLocale($faker->randomElement(['fr', 'en']));
            $manager->persist($u);
            $buyers[] = $u;
        }

        // =============================================
        // 3. PRODUITS
        // =============================================
        // Format : [nom, index_catégorie, prix_min, prix_max]
        $productData = [
            ['Figurine Luffy Gear 5',            0, 45, 120],
            ['Figurine Gojo Satoru',             0, 50, 150],
            ['Figurine Nezuko Kamado',           0, 35, 90],
            ['Figurine Levi Ackerman',           0, 40, 110],
            ['Figurine Tanjiro Kamado',          0, 30, 85],
            ['One Piece Tome 1',                 1, 7, 10],
            ['Jujutsu Kaisen Tome 12',           1, 7, 10],
            ['Solo Leveling Tome 4',             1, 8, 12],
            ['Chainsaw Man Tome 8',              1, 7, 10],
            ['Re:Zero Light Novel Vol. 3',       1, 10, 15],
            ['Porte-clés Attack on Titan',       2, 5, 12],
            ['Mug My Hero Academia',             2, 8, 15],
            ['Badge Hunter x Hunter',            2, 3, 8],
            ['Poster Demon Slayer Hashira',      3, 10, 25],
            ['Poster One Piece Wano',            3, 10, 25],
            ['Poster Naruto Team 7',             3, 12, 20],
            ['Peluche Pikachu',                  4, 15, 35],
            ['Peluche Totoro',                   4, 20, 45],
            ['Peluche Kirby',                    4, 15, 30],
            ['Cape Akatsuki',                    5, 25, 50],
            ['Bandeau ninja Konoha',             5, 8, 18],
            ['Katana décoratif Rengoku',         5, 35, 80],
            ['Zelda Breath of the Wild',         6, 30, 60],
            ['Final Fantasy VII Remake',         6, 25, 50],
            ['Persona 5 Royal',                  6, 20, 45],
            ['Booster Pokémon Écarlate & Violet',7, 4, 8],
            ['Deck Yu-Gi-Oh! Structure',         7, 10, 18],
            ['Booster One Piece Card Game',      7, 4, 8],
            ['Lampe LED Luffy',                  8, 20, 40],
            ['Lampe LED Gojo',                   8, 22, 42],
            ['T-shirt Demon Slayer',             9, 15, 30],
            ['Sweat One Piece',                  9, 30, 55],
        ];

        $countries = ['Japon', 'Corée du Sud', 'Chine', 'Taïwan', 'France'];
        $tags = ['promo', 'nouveau', 'best', 'collector', null];

        $products = [];
        foreach ($productData as [$name, $catIdx, $minPrice, $maxPrice]) {
            $p = new Product();
            $p->setName($name);
            $p->setDescription($faker->sentence(12));
            $p->setPrice($faker->randomFloat(2, $minPrice, $maxPrice));
            $p->setStock($faker->numberBetween(0, 50));
            $p->setStockThreshold(5);
            $p->setCountry($faker->randomElement($countries));
            $p->setTag($faker->randomElement($tags));
            $p->setActive(true);
            $p->setCategory($categories[$catIdx]);
            $p->setSeller($faker->randomElement($sellers));
            $manager->persist($p);
            $products[] = $p;
        }

        // =============================================
        // 4. COMMANDES + LIGNES
        // =============================================
        $statuses = ['pending', 'confirmed', 'shipped', 'cancelled'];

        for ($i = 0; $i < 8; $i++) {
            $order = new Order();
            $order->setReference('CMD-' . strtoupper(uniqid()));
            $order->setStatus($faker->randomElement($statuses));
            $order->setBuyer($faker->randomElement($buyers));

            $total = 0;
            $selectedProducts = $faker->randomElements($products, $faker->numberBetween(1, 4));

            foreach ($selectedProducts as $prod) {
                $line = new OrderLine();
                $line->setRelatedOrder($order);
                $line->setProduct($prod);
                $qty = $faker->numberBetween(1, 3);
                $line->setQuantity($qty);
                $line->setUnitPrice($prod->getPrice());
                $manager->persist($line);
                $total += $prod->getPrice() * $qty;
            }

            $order->setTotal($total);
            $manager->persist($order);
        }

        // =============================================
        // 5. AVIS + NOTES SUR LES VENDEURS
        // =============================================
        foreach ($sellers as $seller) {
            $nbReviews = $faker->numberBetween(2, 5);
            for ($i = 0; $i < $nbReviews; $i++) {
                $review = new Review();
                $review->setContent($faker->paragraph(2));
                $review->setRating($faker->numberBetween(1, 5));
                $review->setAuthor($faker->randomElement($buyers));
                $review->setSeller($seller);
                $manager->persist($review);
            }
        }

        // =============================================
        // 6. FAVORIS
        // =============================================
        foreach ($buyers as $buyer) {
            $favs = $faker->randomElements($products, $faker->numberBetween(2, 6));
            foreach ($favs as $fav) {
                $buyer->addFavorite($fav);
            }
        }

        $manager->flush();
    }
}