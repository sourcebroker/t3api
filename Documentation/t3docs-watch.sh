#!/bin/zsh

cd .. && source <(docker run --rm t3docs/render-documentation show-shell-commands) && dockrun_t3rd makehtml
