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

My idea was very simple - just create function which will returns detected Bootstrap device (`xs`, `sm`, `md`, or `lg`).
A [`getBootstrapDevice`] function simply prepends document body by "testers" which are styled as invisible.
The [`getBootstrapDevice`] just check which "tester" is currently visible.



## The implementation

{% gist e23834deff7d11bb516e getBootstrapDevice.js %}


### Simple demo

```javascript
$(function() {
    var prevDevice = null, onResize = function () {
        var device = getBootstrapDevice();
        if (device != prevDevice) {
            prevDevice = device;
            onDeviceChange(device);
        }
    }, onDeviceChange = function (device) {
        console.log(device); // replace by your code
    };
    $(window).resize(onResize());
    onResize();
});
```

This example will print the active devices on the console.
You can **customize it by reimplementing `onDeviceChange`** method.



[`getBootstrapDevice`]:(https://gist.github.com/petrknap/e23834deff7d11bb516e)
