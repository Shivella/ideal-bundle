Mollie iDeal bundle
===================

This Symfony bundle adds support for iDeal payments by Mollie.

Installation
------------
Installation is a quick 3 step process:

1. Download ideal-bundle using composer
2. Enable the Bundle in AppKernel.php
3. Configure Mollie credentials


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
    $mollie = $this->get('mollie');
    
    $form = $this->createForm(IdealType::class, $mollie->getBanks());
    $form->handleRequest($request);

    if ($form->isValid()) {
        
        $bank = new Bank($form->getData()['banks'], 'bank');
        $amount = (float) 120.99;
        $redirectUrl = $this->generateUrl('route_to_confirm_action', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        return $mollie->execute($bank, $amount, $redirectUrl);
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

