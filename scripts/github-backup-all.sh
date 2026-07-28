#!/bin/bash

set -exu

cd "$1"

do_backup () {
    _out="$1"
    shift
    env PATH=/software/treeoflife/conda/users/envs/toladmin/tolsoft/github_backups/bin/:$PATH \
    github-backup \
    --token-from-gh \
    --repositories --bare --prefer-ssh \
    --wikis \
    --incremental --incremental-by-files \
    --issues --issue-comments --issue-events \
    --pulls --pull-comments --pull-reviews --pull-commits --pull-details \
    --attachments \
    --releases \
    -o "$_out" \
    "$@"
}

do_backup_user () {
    do_backup "$1" "$1"
}

do_backup_user_repo () {
    do_backup "$1" -R "$2" "$1"
}

do_backup_own_org () {
    do_backup "$1" --private --fork  "$1"
}

do_backup_other_org () {
    do_backup "$1" --fork  "$1"
}

for _org in sanger-tol
do
    do_backup_own_org "$_org"
done

for _other_org in tolkit darwintreeoflife CobiontID genomehubs
do
    do_backup_other_org "$_org"
done

for _user in c-zhou thegenemyers KamilSJaron
do
    do_backup_user "$_user"
done

do_backup_user_repo marcelauliano MitoHiFi
do_backup_user_repo dfguan purge_dups

