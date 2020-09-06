#!/bin/zsh
cd ..
source <(docker run --rm t3docs/render-documentation show-shell-commands) && dockrun_t3rd makehtml \
&& open "file:///$(pwd)/Documentation-GENERATED-temp/Result/project/0.0.0/Index.html"
