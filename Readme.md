# Test website

## Create project

```Shell
composer create-project symfony/skeleton .
composer require annotations doctrine mailer twig twig/extensions
composer require --dev profiler
```

## Generate Webpack (CSS & JavaScript)

```Shell
# Build production
yarn build

# Build development
yarn build:dev
```
