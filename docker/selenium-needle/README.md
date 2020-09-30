# Selenium + Needle

Docker image contains **all necessary for visual testing**.
See [`example.py`](example.py) to know how to write basic test
and [`bin/nosetests.sh`](bin/nosetests.sh) to know how to run it.

For first time run `bin/nosetests.sh example.py --with-save-baseline` and create baseline.
Another time run `bin/nosetests.sh example.py` to compare current state against baseline.
Use `host.local` to access your localhost or take advantage of your hosts file.

For more details visit [Needle](https://needle.readthedocs.io/), [Selenium](https://selenium-python.readthedocs.io/) and [Nose](https://nose.readthedocs.io/) documentations.
