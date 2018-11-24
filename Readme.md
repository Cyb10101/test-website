# Test website

## Create project

```Shell
composer create-project symfony/skeleton .
composer require annotations doctrine mailer twig twig/extensions
composer require --dev profiler
```

## Generate Webpack (CSS & JavaScript)

```Shell
yarn run encore dev --watch
yarn run encore production
```
