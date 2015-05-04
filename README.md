BtnMailerBundle
==============

Symfony bundle for easy mail sending

=============

### Step 1: Add MailerBundle in your composer.json (private repo)

```js
{
    "require": {
        "bitnoise/mailer-bundle": "dev-master",
    },
    "repositories": [
        {
            "type": "composer",
            "url": "http://bitnoise.github.io/packages/"
        }
    ],
}
```

### Step 2: Enable the bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Btn\MailerBundle\BtnMailerBundle(),
    );
}
```

### Step 3: Setup config

``` yml
# app/config/config/config.yml
# ...
btn_mailer:
    # custom maier service (optional)
    service: app.mailer
    from_email: no-reply@bitnoi.se
    from_name:  Bitnoi.se
    templates:
        message:
            name: 'Sending regular message'
            template: BtnAppBundle:Mail:message.html.twig
            context_fields:
                message:
                    type: integer
                    param_converter: BtnControlBundle:Message
                    options:
                        required: true
                        label: 'Message'
```
