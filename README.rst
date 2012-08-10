===============
multidomain-sso
===============

Proof of concept for multi-domain single-sign on for PHP applications.

General Presentation
====================

What is it ?
------------

Say you have HTTP domains ``foo.com`` and ``bar.org`` part of the same
authentication domain. Which means that authentication from
http://foo.com and http://bar.org uses the same database for
authentication, and users are able to log in both domain with their
password.

And you want to manage a single-sign on, which would mean for instance:

  1 - open http://foo.com, sign in.

  2 - go to http://bar.org, hurray ! you are recognized and you are
    already signed in with no action from you.

This is not about centralization of authentication. Authentication
mecanism is a separate concern.

This is about circonventing the fact that when connecting to a domain for
the first time, the browser and the server have no clues allowing you to be
recognized. So how could we manage to log in other non-visited website ?

Demo
----

Want to test-it ? check:

  - http://foo.demo.simplee.fr/multidomain-sso/php
  - http://bar.demo.simplee.fr/multidomain-sso/php

These domain are linked. If it doesn't seem to work with you, please send
me a issue request !

How does it works ?
-------------------

The trick is quite simple and not new: when logging in ``foo.com`` a
silent AJAX call will make your browser visit ``bar.org`` setting up
session information between the browser and the server and effectively
log you in ``bar.org``.  Thus, your future "first" visit to
``bar.org`` won't be the real first time the browser and the server
communicates.


How do you circumvents all javascript cross domain restrictions ?
-----------------------------------------------------------------

By using HTTP headers accordingly to CORS_.

.. _CORS: https://developer.mozilla.org/en-US/docs/HTTP_access_control


Do you store the login password and send it to other domains ?
--------------------------------------------------------------

No, you shouldn't store password anywhere. What is sent are tokens
identifying an already opened connection. These tokens are
often called "session ids" and have the appearance of a random hex
fingerprint string generated at login time.


Can I re-use some parts ?
-------------------------

If you find anything useful please feel free to borrow ideas and
code. Any comments, examples or code is welcome also.


Usage
=====

Overview
--------

``auth.php`` provides the abstract class that needs to be implemented
with various subclasses. It needs:

    - an ``AuthProvider``, which is used as the authentication backend
    - an ``AuthTokenStore``, which is used to store locally authentication tokens
    - an ``AuthWebTransmitter``, which is responsible of sending auth tokens

``oeauth.php`` shows how to build a custom class. This one uses OpenERP
as authentication backend, and classical PHP ``$_SESSION`` magic
variable for session token storage. And a re-usable Javascript pattern is used
as a way to propagate tokens to other domains.

You could for example, replace the ``AuthProvider`` class to change
the authentication backend, without changing much in both two other
(you might need to change code related to the session tokens that your
new ``AuthProvider`` will produce and require).


Requirements
------------

This packages requires php-oe-json_ which itself will require
Tivoka_ (use this link to get our fork of tivoka with our mandatory patches).

.. _php-oe-json: https://github.com/simplee/php-oe-json
.. _tivoka: https://github.com/simplee/tivoka

