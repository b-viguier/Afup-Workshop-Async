# Workshop: Asynchronous computation and generators

An [AFUP Lyon](http://lyon.afup.org/) event.

[Here the slides to the associated talk.](https://b-viguier.github.io/downloads/talks/Afup-GeneratorsAndAsync.pdf)


## Introduction

Welcome! 🖐😀

This repository contains a workshop written in [Php](http://php.net/),
to understand how works asynchronous computation with generators.
To achieve this goal, we will build our own asynchronous micro-framework step by step,
and write a small program to crawl a *mysterious* API.

⚠️ Although this document tries to be as complete as possible, some details may be omitted,
and meant to be discussed face to face.

💡Feel free to open a PR/issue if you have any correction/question about this 😀.

## Requirements

* [`php >=7.0`](https://secure.php.net/manual/en/install.php)
 with [CLI](https://secure.php.net/manual/en/features.commandline.introduction.php) support,
* [`php-curl`](https://secure.php.net/manual/en/book.curl.php) extension,
* [`git`](https://git-scm.com/),
* a connection to Internet.

You don't need to already be a Php guru, but it's recommended to be familiar with:
* [Classes and Objects](https://secure.php.net/manual/en/language.oop5.php)
* [Exceptions](https://secure.php.net/manual/en/language.exceptions.php)
* Notions of [unit testing](https://en.wikipedia.org/wiki/Unit_testing)

And of course: patience, perseverance and good mood 🙂.

## Getting Started

### Installation

You only need to clone this repository, and to go in corresponding directory.
```bash
git clone git@github.com:b-viguier/Afup-Workshop-Async.git
cd Afup-Workshop-Async
```

### Folders structure

* `api`: Contains static files for the *mysterious* API.
* `src`: This is **your** working folder, where you will add files and commit them.
* `src_definitions`: Contains interfaces that you will have to implement. Their content will evolve at each step.
* `src_help`: If you are stuck, this folder will give you some solutions to understand how to go ahead.
* `src_server`: The small tool I used to generate the static API, please don't care about it…
* `tests`: Contains unit/functional tests that will drive you to your goal.
* `vendor`: [Composer](https://getcomposer.org/) dependencies. They are versioned, you don't even need to install them.


### Workflow

The workshop is divided into several steps.
For each step:
* Rebase your work to corresponding step with `git rebase step-<n>` (where `<n>` is the expected step number).
* Read carefully the corresponding description in this documentation.
* Take a look in the `test` folder to understand the expected behavior of this step.
* Write requested classes in you `src` folder **using the namespace `Workshop\Async`**.
  Don't forget to implement interfaces from `src_definitions` (namespace `Workshop\Async\Definitions`)
  to guide you!
* Launch tests (regularly if needed) to check if your code has expected behavior,
  using command `./vendor/bin/phpunit`. (Do you know [`PhpUnit`](https://phpunit.readthedocs.io/en/7.1/)?)
* If you need some clues, you can use `git rebase step-<n>-solution` to fill `src_help` folder with pre-written classes.
  Just copy what you need in your own `src` folder.
* Once you are done, don't forget to commit your work: `git add src/*`, `git commit -m "This is my step <n>"`.

Of course, never hesitate to ask questions! 😉

## Part A

In this part, we will write all the tools to create our crawler with *asynchronous design*… **but synchronously**!
Thus, we will focus on generators usage, and promises concepts before to dive in asynchrony.


### Step 1: Waiting a promise

Let's start with the key concept of asynchronous computation: the promise.
But our firsts promises are *constant*,
it means that we can create them only if we know in advance what the result will be,
like in a classical synchronous program.
* The `SuccessPromise` is in state `fulfilled` it means that we can retrieve its final *value*.
* The `FailurePromise` is in state `rejected`, we can only retrieve corresponding exception.

Then we create our initial `EventLoop` and its
`wait` method, a **synchronous** way to retrieve the resolved value of a promise.
If the promise is rejected, this method must throw corresponding exception.
`wait` should be called only once on the *global* promise of your program,
but we will detail this later.


### Step 2: A promise from a generator

A [generator](https://secure.php.net/manual/en/class.generator.php) will be the only way to wait a promise *asynchronously*,
using the keyword [`yield`](https://secure.php.net/manual/en/language.generators.syntax.php#control-structures.yield).
By doing this, our `EventLoop` will have *time* to execute other generators,
and delivering the expected promise result when known.

But how to execute an asynchronous generator?
By creating a promise, with the method `async`,
that will be resolved when generator return a value (success), or throw an exception (failure).

⚠️ Remember that for the moment, we don't yet focus about how to execute several generators asynchronously.
The `async` method has just to execute the generator, transmit expected values from *yielded* promises,
and create a *constant* promise from the generator result.


### Step 3: Grouping promises

Generators give ability to write asynchronous functions, but not to run them concurrently.
It's the role of the `all` method,
to create a promise that will be resolved when all children promises are resolved.
If one child promise fails, the parent promise hase to fail too. 


### Step 4: An Http client

We won't write a fully [Psr-7](https://www.php-fig.org/psr/psr-7/) compatible Http client,
we just need a way to create a promise resolved with the response.

💡 *Keep it simple!* 
It's not yet the time to play with `curl`.
Do you remember that [`file_get_contents`](https://secure.php.net/manual/en/function.file-get-contents.php)
can be used to perform [Http requests](https://secure.php.net/manual/en/function.file-get-contents.php#refsect1-function.file-get-contents-examples)?


### Step 5: Crawling the *mysterious* text-API…

Here the first challenge, writing a CLI program to crawl an API with an **huge** number of requests.
Feel free to use CLI tools/libraries you are comfortable with, there is no predefined structure here, no tests to execute.
Of course, you have to use our predefined `HttpClient` and a lot of asynchronous requests,
even if our `EventLoop` is synchronous.
Then we will compare performances with our actual asynchronous `EventLoop` in part B. 

You have to discover a *text*, composed by *sentences*, composed by *words*, composed by *letters*.
* The first API call gives you the list of ordered sentences:
[https://b-viguier.github.io/Afup-Workshop-Async/api/text.json](https://b-viguier.github.io/Afup-Workshop-Async/api/text.json)
* Each sentence is accessible according to the given relative path: 
[https://b-viguier.github.io/Afup-Workshop-Async/api/sentence/a53441.json](https://b-viguier.github.io/Afup-Workshop-Async/api/sentence/a53441.json) for example.  
* In the same way, you can retrieve details about words composing a sentence:
[https://b-viguier.github.io/Afup-Workshop-Async/api/word/5e2f8e.json](https://b-viguier.github.io/Afup-Workshop-Async/api/word/5e2f8e.json)
* To finish, the value of each letter can be requested:
[https://b-viguier.github.io/Afup-Workshop-Async/api/letter/7fc562.json](https://b-viguier.github.io/Afup-Workshop-Async/api/letter/7fc562.json)

To retrieve the full text, you will need about **8000 requests**.
Please, don't try to use cache system now, it will be less fun 😉.
If each request takes 200ms, it means that the global execution should take about 25 minutes.
To be honest, I never had the patience to wait until the end 😅,
that's why you should use a local Php server to test your program.
It may be Apache, Nginx… or just the [built-in Php web server](https://secure.php.net/manual/en/features.commandline.webserver.php):
 ```bash
cd api
php -S localhost:1234
```
Then you can start with http://localhost:1234/text.json to retrieve the full text in less than 20 seconds!

⚠️ If you code it in *bare Php*, do not forget to `include __DIR__.'/../vendor/autoload.php';`
in order to enable [classes autoloading](https://secure.php.net/manual/en/language.oop5.autoload.php).

💡 Think about a way to follow the progress of your program, and possible encountered errors.



## Part B

Now that we have our crawler, we will empower our micro-framework to actually use asynchrony,
and then compare performances.

### Step 6: Pending promise and asynchronous event loop

In step 1, we coded simple *constant* promises, but that's not the good way to go.
We need a third state, a *pending* promise that may be resolved or rejected **later**.
So here we go, write the `PendingPromise` class, and adapt all `EventLoop` methods to handle this new workflow.

💡⚠️ It's a big step, here some hints.
* The `wait` function should loop until the input promise is no more pending.
* The `async` method is no more in charge of generators execution, it's the role of the loop in the `wait` function.
* To wait several promises concurrently, the `all` method must ensure that they will be handled by the event loop.
Why not rely on a generator?


### Step 7: Idle promise and asynchronous Http client

How can we perform an Http request and be notified when the response is ready?
It's possible to rely on [`curl` extension](https://secure.php.net/manual/en/book.curl.php)
with its [`curl_multi_*`](https://secure.php.net/manual/en/function.curl-multi-init.php) set of functions.
This [`curl_mutli_info_read` example](https://secure.php.net/manual/en/function.curl-multi-info-read.php#refsect1-function.curl-multi-info-read-examples)
gives you a good overview of what the process should be.

You certainly remarked that you need to loop to wait the response.
That's exactly why we need the `EventLoop::idle` function:
if you wait asynchronously for an `idle` promise in a loop,
you won't block other `EventLoop`'s tasks. 

💡 Do you know that you can cast a [`curl` resource to an `int` value](https://secure.php.net/manual/en/language.types.integer.php#language.types.integer.casting)? 


### Step 8: Asynchronous crawler

Let's see if our actually asynchronous event loop is really better!
Normally, you won't have to modify your program so much.
Don't try to improve performances with the built-in Php web server,
it can run only one request at a time, so asynchronous requests won't help.

💡 Since your program perform a **huge** number of request,
your computer will certainly complains, and generate **a lot** of errors…
Don't worry, just handle them and… retry! 😅

 
## Part C

Congratulations, we did it!
But what other about existing frameworks? Can we wrap them into our classes?
* [Amp](https://amphp.org/) is a framework using generators too,
it's a good way to see how it deals with them.
* [ReactPhp](https://reactphp.org/) use [Promises/A](https://reactphp.org/promise/) implementations,
you'll have to convert their promises with generators.

⚠️ You will have to build your own steps from here…


## Part D

You have now a strong experience about asynchronous programming,
and our micro-framework can be improved in many ways. 

* The `PromiseInterface` defines a lot of functions, but they are only used by our event loop.
Why should we allow someone to check the state of a promise?
It could be a very bad practice to let a developer looping over a promise state.
[*"Make Interfaces Easy to Use Correctly and Hard to Use Incorrectly"*](https://www.safaribooksonline.com/library/view/97-things-every/9780596809515/ch55.html).
Can our micro-framework provide an empty `PromiseInterface`?
* If we want to provide some adapters with other frameworks,
it will be easier to use the [Factory pattern](https://en.wikipedia.org/wiki/Factory_%28object-oriented_programming%29)
and our `EventLoop` seems to be the good place to create dedicated promises.
* It's not very safe to launch too much requests at the same time.
It could be interesting to limit concurrent requests,
by acquiring a *token* to perform a request.
Of course, token will be limited, and we will use promises to deliver them.
* Our program requests the same urls a lot of time… what about an in-memory cache?
Let's use [Proxy pattern](https://en.wikipedia.org/wiki/Proxy_pattern) and promises
to handle this case.
* It can be a *very* bad idea to loop infinitely, without doing anything.
Php will consume a lot of CPU for nothing, slowing down your machine for other tasks
(network operations for example).
We could improve our event loop to [`usleep`](https://secure.php.net/manual/en/function.usleep.php)
a little between two ticks if there is *nothing to do*.

And then, feel free to suggest your own improvements 🙂.