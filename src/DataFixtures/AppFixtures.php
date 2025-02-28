<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Band;
use App\Entity\Event;
use App\Entity\Country;
use App\Entity\User;
use App\Entity\Review;
use App\Entity\Picture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\Provider\MetalAddictProvider;
use DateTime;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $connection = $manager->getConnection();
        $connection->query('SET foreign_key_checks = 0');
        $connection->query('TRUNCATE TABLE picture');
        $connection->query('TRUNCATE TABLE review');
        $connection->query('TRUNCATE TABLE event_user');
        $connection->query('TRUNCATE TABLE user');
        $connection->query('TRUNCATE TABLE event');
        $connection->query('TRUNCATE TABLE band');
        $connection->query('TRUNCATE TABLE country');

        $faker = Factory::create('fr_FR');
        $faker->addProvider(new MetalAddictProvider());
        $faker->seed('Metal Addict');

        $bands = [];
        foreach ($faker->getBands() as $bandData) {
            $band = new Band();
            $band->setName($bandData['name']);
            $band->setMusicbrainzId($bandData['musicbrainzId']);
            $manager->persist($band);
            $bands[] = $band;
        }
        
        $countries = [];
        foreach ($faker->getCountries() as $countryData) {
            $country = new Country();
            $country->setName($countryData['name']);
            $country->setCountryCode($countryData['countryCode']);
            $manager->persist($country);
            $countries[] = $country;
        }

        /*
        $users = [];
        foreach ($faker->getUsers() as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setPassword($this->encoder->encodePassword($user, 'oMetal'));
            $user->setRoles($userData['roles']);
            $user->setNickname($userData['nickname']);
            $user->setBiography($faker->words(20, true));
            $manager->persist($user);
            $users[] = $user;
        }

        $pictures = $faker->getPictures();
        foreach ($faker->getEvents() as $eventData) {
            $event = new Event();
            $event->setSetlistId($eventData['setlistId']);
            $event->setVenue($eventData['venue']);
            $event->setCity($eventData['city']);
            $event->setDate(DateTime::createFromFormat('d-m-Y', $eventData['date']));
            $event->setBand($bands[random_int(0, count($bands) - 1)]);
            $event->setCountry($countries[random_int(0, count($countries) - 1)]);
            shuffle($users);
            for ($i = 0; $i < random_int(0, count($users)); $i++) { 
                $event->addUser($users[$i]);
                if (random_int(0, 1) === 1) {
                    $review = new Review();
                    $review->setTitle($faker->words(5, true));
                    $review->setContent($faker->text());
                    $review->setEvent($event);
                    $review->setUser($users[$i]);
                    $manager->persist($review);
                }
                shuffle($pictures);
                for ($j = 0; $j < random_int(0, count($pictures)); $j++) {
                    $picture = new Picture();
                    $picture->setPath($pictures[$j]);
                    $picture->setEvent($event);
                    $picture->setUser($users[$i]);
                    $manager->persist($picture);
                }
            }
            $manager->persist($event);
        }
        */

        $manager->flush();
    }
}
