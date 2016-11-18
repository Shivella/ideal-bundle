Mollie iDeal bundle
===================

This Symfony3 bundle adds support for [iDEAL](https://www.mollie.com/ideal/) payments by Mollie.
It is using [Mollie-php-api](https://github.com/mollie/mollie-api-php/). A Mollie account is required. 

For more information see [Mollie](https://www.mollie.com/)

[![Build Status](https://travis-ci.org/Shivella/ideal-bundle.svg?branch=master)](https://travis-ci.org/Shivella/ideal-bundle) [![Latest Stable Version](https://poser.pugx.org/shivella/ideal-bundle/v/stable)](https://packagist.org/packages/shivella/ideal-bundle) [![License](https://poser.pugx.org/shivella/ideal-bundle/license)](https://packagist.org/packages/shivella/ideal-bundle) [![Total Downloads](https://poser.pugx.org/shivella/ideal-bundle/downloads)](https://packagist.org/packages/shivella/ideal-bundle)

Installation
------------
Installation is a quick 3 step process:

1. Download ideal-bundle using composer
2. Enable the Bundle in AppKernel.php
3. Configure your Mollie credentials

### Step 1: Download ideal-bundle using composer

Add UsoftIDealBundle by running the command:

``` bash
$ composer require shivella/ideal-bundle
```

### Step 2: Enable the Bundle in AppKernel.php


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

### Step 3: Configure Mollie credentials
```yaml
# app/config/config.yml

# ideal Mollie
usoft_i_deal:
    mollie:
        key: secret_mollie_key
        description: "Mollie payment"

```


Usage in Controller
-------------------


``` php
<?php
// Acme/Bundle/OrderController.php

public function paymentAction(Request $request)
{   
    $form = $this->createForm(IdealType::class);
    $form->handleRequest($request);

    if ($form->isValid()) {
    
        $mollie = $this->get('mollie');
        $bank = new Bank($form->get('bank')->getData());
        $amount = (float) 120.99;

        return $mollie->execute($bank, $amount, 'route_to_confirm_action');
    }
    
    return $this->render('payment.html.twig', ['form' => $form->createView()]);
}

/**
 * @Route("/order/confirm", name="route_to_confirm_action")
 *
 * @param Request $request
 */
public function confirmAction(Request $request)
{
    if ($this->get('mollie')->confirm($request)) {
        // handle order....
    } else {
        // Something went wrong...
    }
}
```

