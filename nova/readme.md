# Laravel Nova 👩‍🚀

![Build Status](https://app.chipperci.com/projects/8d0bc3d0-073f-4bfd-83f3-4a9879a9aaab/status/master)

- [Website](https://nova.laravel.com)
- [Releases](https://nova.laravel.com/releases)
- [Documentation](https://nova.laravel.com/docs)
  - [Installation](https://nova.laravel.com/docs/3.0/installation.html)
  - [Updating Nova](https://nova.laravel.com/docs/3.0/installation.html#updating-nova)
- [Nova Packages](https://novapackages.com)

## Upgrade Guide

- Copy the `Main` dashboard to your codebase
- Delete the `cards` method from your `NovaServiceProvider`
- Action `fields()` method changed to `fields(NovaRequest $request)`

### Modals

If you have a custom modal, make sure to add the `<teleport to="#modals">` component as the root level.
