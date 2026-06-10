#!/bin/bash -l

source /software/treeoflife/etc/slack_webhooks_env.sh

script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)

exec env "SLACK_WEBHOOK_URL=${SLACK_WEBHOOK_TOL_PIPELINES}" WEEKS=1 "${script_dir}/github_prs.py"
