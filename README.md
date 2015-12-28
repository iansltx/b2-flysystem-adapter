b2-flysystem-adapter
====================

Pardon the dust...the Backblaze B2 client is nearly done for this project but the Flysystem adapter isn't.

Once the client's API is stable I'll move it into its own package and require it from the Flysystem adapter. That way,
you won't have to pull in Flysystem to take advantage of the B2 client.

With that said, the intent of this project is to build a more fluent abstraction layer in PHP around Backblaze's B2
service, then use that abstraction to hook into Flysystem's own abstraction layer. As fair warning, B2 does a few things
quite differently than your standard cloud storage system, so some features (like moving or copying a file) aren't
currently available.

Installing
==========

Point Composer to this repo manually via a `vcs` key. This package will be on Packagist when it's a little more stable.

Usage
=====

*NOTE:* The Flysystem adapter isn't built yet, so trying to instantiate it will throw a LogicException for now.

All B2 API calls have been, either directly or as part of a larger flow (e.g. file upload or authentication) been
implemented in B2Client\Client, but you'll need to take a look at the code for the moment to see how to use them.
Here's a taste, though:

```php

use iansltx\B2Client\Client;
use iansltx\B2Client\Credentials;
use iansltx\B2Client\BucketVisibility;

include __DIR__ . '/vendor/autoload.php';

// set up credentials based on app ID and secret, or store intermediate creds in a ServerSettings object
// to avoid the extra API round-trip
$client = new Client(new Credentials($_SERVER['B2_APP_ID'], $_SERVER['B2_APP_SECRET']));

// pick a different bucket name; this one's almost certainly in use, and bucket names are global
$newBucket = $client->createBucket('test-bucket', BucketVisibility::ALL_PRIVATE);

// new bucket is in the list!
foreach ($client->listBuckets() as $bucket) {
    echo "Name: " . $bucket->getName() . "; ID: " . $bucket->getId() . "\n";
}

$client->deleteBucketById($newBucket->getId());

// new bucket is gone!
foreach ($client->listBuckets() as $bucket) {
    echo "Name: " . $bucket->getName() . "; ID: " . $bucket->getId() . "\n";
}
```

Contributing
============

Contributions (in the form of PSR-1, -2 and -4 compliant pull requests) are greatly appreciated, though I reserve the
right to ask PRers to keep their changes on their fork.
