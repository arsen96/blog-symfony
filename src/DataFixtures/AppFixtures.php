<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Post;
use DateTimeImmutable;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AppFixtures extends Fixture
{

    public function __construct(UserPasswordHasherInterface $userHasher)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for($i=1;$i< 200;$i++){
            $category = $this->getRandomCategory($manager);
            if (!$category) {
                throw new \Exception('Category not found ');
            }

            $post = $manager->getRepository(Post::class)->findAll();
            $post = new Post();
            $post->setTitle($faker->words(5,true))
                    ->setDescription($faker->realText(2000))
                    ->setImg("https://picsum.photos/1900/2000?image=$i")
                   ->setCreatedAt(DateTimeImmutable::createFromMutable($faker->dateTime));
            $post->addCategory($category);
            $manager->persist($post);
        }

        $manager->flush();
    }

    public function getRandomCategory($manager):Category{
        $category = $manager->getRepository(Category::class)->findAll();
        $rand_id_index = array_rand($category);
        $category = $category[$rand_id_index];
        $category = $manager->getRepository(Category::class)->findOneBy(["id" => $category->id]);
        return $category;
    }
}
