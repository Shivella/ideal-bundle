iDeal-bundle
============

The iDeal bundle adds support for transferring iDeal payments with provider such as mollie, easy ideal.
iDeal-bundle provides services which can used in Symfony3 for requesting iDeal api calls.

Installation
------------
Installation is a quick 3 step process:

1. Download UsoftIDealBundle using composer
2. Enable the Bundle
3. Configure your ideal provider


### Step 1: Download iDeal-bundle using composer

Add UsoftIDealBundle by running the command:

``` bash
$ composer require shivella/ideal-bundle
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

usoft_i_deal:
    providers:
        easy_ideal:
            id: my_secret_id
            key: my_secret_key
            secret: my_secret_secret
            description: easy ideal payment
```


Usage in Controller
-------------------


``` php
<?php
// Acme/WebshopBundle/OrderController.php

public function paymentAction(Request $request)
{
    $easyIdeal = $this->get('mollie');
    
    $form = $this->createForm(IdealType::class, $easyIdeal->getBanks());
    $form->handleRequest($request);

    if ($form->isValid()) {
        
        $bank = new Bank($form->getData()['banks'], 'bank');
        $amount = (float) 120.99;
        $redirectUrl = $this->generateUrl('route_to_confirm_action', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        return $easyIdeal->execute($bank, $amount, $redirectUrl);
    }
    
    return $this->render('payment.html.twig', ['form' => $form->createView()]);
}

public function confirmAction()
{
    if ($this->get('mollie')->confirm()) {
        // handle order....
    } else {
        // Something went wrong...
    }
}
```

