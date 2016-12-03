---
layout: blog.post
title: "Organize your #music by markers and #PHP #CLI script"
category: cli
keywords:
    - PHP script
    - CLI script
    - music
    - playlist
    - generator
    - terminal
    - console
---

Do you have any favorite songs, songs for relax, work, etc.?
**If yes, how do you organize songs?**
Do you prefer to use folders and create symlinks to song files or to use playlist?
And how do you sync it between devices?
I tried many ways before **I started to use markers**.
So let me introduce markers to you.



## Introduction

Marker is a letter at the end of file name.
If you have a file `song.mp3`, you can **mark them as favorite by adding "F" before extension**.
In this case it will be `song.F.mp3`.
You can **mark them also as relax by adding "R"** and you get `song.FR.mp3`.

You will have many markers at the end probably.
So you can enforce markers positions (for example `FR`) and **use another character (for example `_`) as space**.
The result may look like:

```text
$ tree
.
├── Artist - Song A.FD.mp3
├── Artist - Song B.F_.mp3
└── Artist - Song C._D.mp3

0 directories, 3 files
```

It will helps you with quick detection if the song is new (`song.mp3`) or you should delete it (`song.__.mp3`).



## How to work with it?

If you have your files marked, you can use my [playlist generator].
The [playlist generator] supports `m3u` and `pls` formats and needs [PHP] to run.
You will need script which configures [playlist generator] for you, like this one:

{% gist 4f11249f720cf0213c2f run.php %}

It takes first argument as path to folder with songs and register following markers:

 * `f` for *favorite songs*
 * `r` for *relax songs*
 * `w` for *work songs*
 * `d` for *dynamic songs*

The only thing you need to do is run by `php run.php ./Songs`.
The [playlist generator] will creates playlists in working directory.



## Why am I using markers?

I need to **organize my music over many devices** (notebook, desktop, car, etc.) via file synchronization.
It helps me to keep my music files clear - **if a song lost last marker I can painlessly delete them**.

If you like this idea, **let's enjoy it and share it** with your friends.



[playlist generator]:https://gist.github.com/petrknap/4f11249f720cf0213c2f#file-playlistgenerator-php
[PHP]:https://secure.php.net/
