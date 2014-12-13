Contributing Guidelines
=======================

This document details the process you should follow when contributing code.

Overview
--------

The master branch contains active development. It should be considered unstable. Versions are tagged in the repository.


Step by step
------------

### 1. Fork this repository.

Fork the nice/framework repository on GitHub.

### 2. Clone the repository and checkout the master branch.

``` bash
git clone git@github.com/path/to/your/fork nice-framework
cd nice-framework
git checkout master
```

### 3. Create a new branch.

``` bash
git checkout -b new-and-awesome
```

### 4. Implement the feature, publish your feature branch to your forked repository.

``` bash
git add .
git commit -m "Made some changes"
git push origin new-and-awesome
```

### 5. Create a pull request from your feature branch to the master branch of this project.

From your repository on the GitHub interface, click the pull request button. Select your feature branch and ensure
the master branch of Nice framework is selected.



Additional Info
---------------

This project follows [Semantic Versioning](http://semver.org). See the [Changelog](CHANGELOG.md) for details on upgrading.
