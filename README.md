iDeal-bundle
============

The iDeal bundle adds support for transferring iDeal payments with provider such as mollie, easy ideal.
iDeal-bundle provides services which can used in Symfony2 for requesting iDeal api calls.

Installation
------------
Installation is a quick 3 step process:

1. Download UsoftIDealBundle using composer
2. Enable the Bundle
3. Configure your ideal provider


### Step 1: Download iDeal-bundle using composer

Add UsoftIDealBundle by running the command:

``` bash
$ composer require shivella/ideal-bundle "dev-master"
```

### Step 2: Enable the bundle


``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Usoft\IDealBundle\UsoftIDealBundle(),
    );
}
```

### Step 3: Configure your config.yml
```yaml
# app/config/config.yml

ideal:
    providers:
        easy_ideal:
            id: my_secret_id
            key: my_secret_key
            secret: my_secret_secret
```


Usage in Controller
-------------------


``` php
<?php
// Acme/WebshopBundle/OrderController.php

public function confirmAction()
{
    $easyIdeal = $easyIdeal = $this->get('easy_ideal');
    
    if ($easyIdeal->confirm()) {
        // handle order....
    } else {
        // Something went wrong...
    }
}

public function sendAction(Request $request)
{
    $easyIdeal = $easyIdeal = $this->get('easy_ideal');
    $redirectUrl = $this->generateUrl('acme_route_name', array(), true);
    $amount = 120.99;
    
    $form = $this->createForm('ideal', $easyIdeal->getBanks());
    $form->handleRequest($request);

    if ($form->isValid()) {
        $bank = new Bank($form->getData()['banks'], 'bank');
        $redirectUrl = $this->generateUrl('usoft_webshop_order_ideal', array(), true);

        return $ideal->execute($bank, $amount, $redirectUrl);
    }
}
```

