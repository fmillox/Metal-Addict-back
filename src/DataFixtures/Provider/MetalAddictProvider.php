<?php

namespace App\DataFixtures\Provider;

class MetalAddictProvider
{
  private $bands = [
    [
      'name' => 'Iron Maiden',
      'musicbrainzId' => 'ca891d65-d9b0-4258-89f7-e6ba29d83767'
    ],
    [
      'name' => 'Pantera',
      'musicbrainzId' => '541f16f5-ad7a-428e-af89-9fa1b16d3c9c'
    ],
    [
      'name' => 'Guns N’ Roses',
      'musicbrainzId' => 'eeb1195b-f213-4ce1-b28c-8565211f8e43'
    ],
    [
      'name' => 'Metallica',
      'musicbrainzId' => '65f4f0c5-ef9e-490c-aee3-909e7ae6b2ab'
    ],
    [
      'name' => 'Faith No More',
      'musicbrainzId' => 'b15ebd71-a252-417d-9e1c-3e6863da68f8'
    ]
  ];

  private $countries = [
    [
      'name' => 'Etats-Unis',
      'countryCode' => 'US'
    ],
    [
      'name' => 'Allemagne',
      'countryCode' => 'DE'
    ],
    [
      'name' => 'France',
      'countryCode' => 'FR'
    ],
    [
      'name' => 'Royaume-Uni',
      'countryCode' => 'GB'
    ],
    [
      'name' => 'Espagne',
      'countryCode' => 'ES'
    ]
  ];

  private $pictures = [
    '/images/pictures/2021/02/26/01.jpg',
    '/images/pictures/2021/02/26/02.jpg',
    '/images/pictures/2021/02/26/03.jpg',
    '/images/pictures/2021/02/26/04.jpg',
    '/images/pictures/2021/02/26/05.jpg',
    '/images/pictures/2021/02/26/06.jpg',
    '/images/pictures/2021/02/26/07.jpg'
  ];

  private $events = [
    [
      'setlistId' => '39d9997',
      'venue' => 'Estadio Nacional',
      'city' => 'Santiago du Chili',
      'date' => '15-10-2019'
    ],
    [
      'setlistId' => '3b9cc444',
      'venue' => 'Cynthia Woods Mitchell Pavilion',
      'city' => 'The Woodlands',
      'date' => '22-09-2019'
    ],
    [
      'setlistId' => '53fc3f61',
      'venue' => 'Troubadour',
      'city' => 'West Hollywood',
      'date' => '20-08-2016'
    ],
    [
      'setlistId' => '2bfccc26',
      'venue' => 'Great American Music Hall',
      'city' => 'San Francisco',
      'date' => '18-08-2016'
    ],
    [
      'setlistId' => '3f4f18f',
      'venue' => '3bd6d854',
      'city' => 'Rio de Janeiro',
      'date' => '25-09-2015'
    ]
  ];

  private $users = [
    [
      'email' => 'jojo@gmail.com',
      'nickname' => 'jojo',
      'roles' => ['ROLE_USER']
    ],
    [
      'email' => 'juju@gmail.com',
      'nickname' => 'juju',
      'roles' => ['ROLE_USER']
    ],
    [
      'email' => 'jaja@gmail.com',
      'nickname' => 'jaja',
      'roles' => ['ROLE_USER']
    ]
  ];

  /**
   * Get the value of bands
   */ 
  public function getBands()
  {
    return $this->bands;
  }

  /**
   * Get the value of countries
   */ 
  public function getCountries()
  {
    return $this->countries;
  }

  /**
   * Get the value of pictures
   */ 
  public function getPictures()
  {
    return $this->pictures;
  }

  /**
   * Get the value of events
   */ 
  public function getEvents()
  {
    return $this->events;
  }

  /**
   * Get the value of users
   */ 
  public function getUsers()
  {
    return $this->users;
  }
}
