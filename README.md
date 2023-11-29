<center>
<img src="resources/img/disdain.svg">
<h1>Anne</h1>
<h3><i>A Laravel discord bot with a web-based UI that uses OpenAI</i></h3>
</center>

## What am I?
This was a project to create a Laravel bot for Discord that used OpenAI to generate dialogue.

We named her Anne, and thus, that is the name of the bot that lives here.

## What can I do?

- Laravel-powered backend
- Web-based multi-tenant dashboard
- An interface to modify, create and delete prompt while the bot is active from the web UI
- OpenAI-powered (Dall-E, Gpt-3.5, Gpt-4, Codex, Embeddings)
- Pinecone vector DB enabled for long-term memory
- A bunch of discord stuff written from scratch like:
  - A trivia game unique to the bot
  - A custom command system
  - A playlist system that captures and stores all youtube, soundcloud and spotify links by user and scores users
  - And a bunch of other stuff finished and unfinished
- Web UI Features:
    - Message history
    - Playlist History
    - Prompt Editor
    - An interface to see anne's "thoughts" for each message she sends
    - Error Logging
    - and more

## Status

Anne is very much unfinished. A lot of her was written just as GPT exploded in popularity, and there weren't libraries for anything, unlike now.
So a lot of optimization could be done. She has a lot of bugs.

The two creators of this project aren't really on speaking terms anymore so it's unlikely that this will ever be finished, thus it's being made public with the hope that maybe someone will be able to do something with the work we've done.


## Requirements

- PHP 8.0
- Laravel 9
- Livewire 2

You should be able to get away with cloning the repo, running `composer install` and `npm install` and then doing a migration.

If you are having trouble getting it to install feel free to get in touch with me and I'll help as best I can.

Note: PHP is not the best language for discord bots, or for AI, or for async or like 14 other parts of this project. You've been warned. <3

<p style="text-align: right"><i>Backend poorly coded in PHP by ficetyeis, frontend by gils 2023-2023</i></p>
