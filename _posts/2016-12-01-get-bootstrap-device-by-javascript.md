---
layout: blog.post
title: "Get a #Bootstrap device by #JavaScript"
category: frontend
keywords:
    - Bootstrap
    - JavaScript
    - responsive scripting
    - responsive design
    - frontend development
---

When you are creating responsive site, sometimes you need to separate non-touch and touch handlers.
Sure, you can have both handlers at same time or detect mobile devices by JavaScript directly.
But **it will be cool to detect only Bootstrap device if you are using Bootstrap**.
It eliminates the problem when your JavaScript and Bootstrap detects different devices.



## The idea

My idea was very simple - just create function which will return detected Bootstrap device (`xs`, `sm`, `md`, or `lg`).
A [`getBootstrapDevice`] function simply prepends document body by "testers" which are styled as invisible.
The [`getBootstrapDevice`] just checks which "tester" is currently visible.



## The implementation

{% gist e23834deff7d11bb516e getBootstrapDevice.js %}


### Simple demo

{% gist e23834deff7d11bb516e demo.html %}

This example prints the active devices on the screen.
You can **customize it by reimplementing `onDeviceChange`** function.



[`getBootstrapDevice`]:https://gist.github.com/petrknap/e23834deff7d11bb516e
