# Logging extension for Job Queue for Yii2 based on Beanstalkd

This extension provides a package extends the job queue implementation with logging.

```bash
$ composer require jc-it/yii2-job-queue-logging
```

or add

```
"jc-it/yii2-job-queue-logging": "<latest version>"
```

to the `require` section of your `composer.json` file.

## Configuration

- Implement `\JCIT\jobqueue\interfaces\JobHandlerLoggerInterface::class` and register it in your DI
- Either add it to you JobHandler or extend `\JCIT\jobqueue\jobHandlers\LoggingHandler::class`
- `\JCIT\jobqueue\events\JobQueueEvent` is triggered on `->put` and `->handle` to enable even more precise logging

## Credits
- [Sam Mousa](https://github.com/SamMousa)
- [Joey Claessen](https://github.com/joester89)
