#!/usr/bin/env bash

commit=$1
if [ -z ${commit} ]; then
    commit=$(git tag --sort=-creatordate | head -1)
    if [ -z ${commit} ]; then
        commit="master";
    fi
fi

# Remove old release
rm -rf FroshPlatformPerformance FroshPlatformPerformance-*.zip

# Build new release
mkdir -p FroshPlatformPerformance
git archive ${commit} | tar -x -C FroshPlatformPerformance
composer install --no-dev -n -o -d FroshPlatformPerformance
( find ./FroshPlatformPerformance -type d -name ".git" && find ./FroshPlatformPerformance -name ".gitignore" && find ./FroshPlatformPerformance -name ".gitmodules" ) | xargs rm -r
zip -r FroshPlatformPerformance-${commit}.zip FroshPlatformPerformance
