---
layout: blog.post
title: "Wait for multiple #XHR with #JavaScript #Semaphore"
category: frontend
keywords:
    - JavaScript
    - XHR
    - AJAX
    - concurrency
    - frontend development
---

If you are playing with XHR, sometimes you need to wait for many small requests.
Sure, you can have separate callback for each request, but sometimes **you need to wait until you load full dataset**.
Of course you can use modern ways like `promise`, but there aren't supported everywhere.


## The idea

My idea was very simple - just create copy of [semaphore from `pthread` library].


## The implementation

{% gist 9103aae2ecce2222e1e5776f98274dab semaphore.js %}


### Simple demo

{% gist 9103aae2ecce2222e1e5776f98274dab demo.html %}

This example **prints your IP address and your user-agent on the screen** in one nice sentence.
If you need to wait for `N` request, simply create `new Semaphore(-N)`.

> If you wish to run `N` methods parallel and you wish to run maximally `M` of them at once,
> simply create `new Semaphore(+M)` and stacks them all by `wait` method.



[semaphore from `pthread` library]:https://www.google.com/search?q=semaphore+pthread
