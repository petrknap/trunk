---
layout: blog.post
title: "Organize your #music by markers and #PHP #CLI script"
category: cli
keywords:
    - music
    - playlist
    - generator
    - markers
    - PHP script
    - CLI script
    - terminal
    - console
---

Do you have any favorite songs, songs for relax, work, etc.?
If yes, **how do you organize your music**?
Do you prefer to use folders or playlists?
If playlists, **how do you sync it between devices and keep it up-to-date**?
I tried many ways before **I started to use markers**.
So let me introduce markers to you.



## Introduction

"Marker" is a letter at the end of file name.
If you have a file `song.mp3`, you can **mark it as favorite by adding "F" before extension**.
In this case it will be `song.F.mp3`.
You can **mark it also as relax by adding "R"** and you get `song.FR.mp3`.

You probably will have many markers at the end.
So you can enforce position of every marker (for example `FR`) and **use another character (for example `_`) as space**.
The result may look like:

```text
$ tree
.
├── Artist - Song A.FD.mp3
├── Artist - Song B.F_.mp3
└── Artist - Song C._D.mp3

0 directories, 3 files
```

It will helps you with quick detection if it is new (`song.mp3`) or unwanted (`song.__.mp3`) song.



## How to work with it?

If you have your files marked, you can use a [playlist generator].
The [playlist generator] supports `m3u` and `pls` formats and needs [PHP] to run.
You will also need script which configures [playlist generator] for you.
Like this one:

{% gist 4f11249f720cf0213c2f run.php %}

An first argument is path to folder with songs and it registers following markers:

 * `f` for *favorite songs*
 * `r` for *relax songs*
 * `w` for *work songs*
 * `d` for *dynamic songs*

The only thing you need to do is run `php run.php ./Songs`.
The [playlist generator] will creates playlists in current working directory.



## Why am I using markers?

I need to **organize my music over many devices** (notebook, desktop, car, etc.) via file synchronization and playlists.
It helps me to keep my music files clear - **if a song lost last marker I can painlessly delete it**.

If you like this idea, **let's enjoy it and share it** with your friends.



[playlist generator]:https://gist.github.com/petrknap/4f11249f720cf0213c2f#file-playlistgenerator-php
[PHP]:https://secure.php.net/
