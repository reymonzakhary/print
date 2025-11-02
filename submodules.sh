#!/bin/bash

# Ensure submodules are initialized
git submodule update --init --recursive

# Switch each submodule to dev branch
# shellcheck disable=SC2016
git submodule foreach '
  echo "Switching to dev in $name"
  git checkout dev || git checkout -b dev origin/dev
  git pull origin dev
'