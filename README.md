# petrknap/trunk

Monolithic repository


## Apps

Compiled applications can be downloaded at https://1drv.ms/f/s!AM62gYHBWCjkj-gL.


## Tests

To run all tests against `HEAD` run `make tests`.
To run all tests against its requirements run `make tests-on-packages`.

### Hints

 * You can **pass parameters to phpunit** via `ARGS`, f.e. `make tests ARGS="--filter=MyClass"`.
 * If you need **read-only access** to files prefix full path by `/mnt/read-only`, f.e. `$roDir = "/mnt/read-only" . __DIR__`.
