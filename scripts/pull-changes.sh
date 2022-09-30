#!/bin/bash

FLAG_NAME="pull"
CURRENT_DIR="$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd)"
PROJECT_DIR="$(realpath $CURRENT_DIR/../)"
FLAGS_DIR="$CURRENT_DIR/../var/flags"
FLAG_FILE="$(realpath $FLAGS_DIR/$FLAG_NAME)"
HELP="0"
DOCKER_COMPOSE_FILE="~/notifier_docker/docker-compose.yml"

function pull_git() {
      cd "$PROJECT_DIR"
      git stash
      git checkout main
      git pull
      git stash apply
}

function stop_docker {
  docker-compose -f "$DOCKER_COMPOSE_FILE" stop || return 1
}

function start_docker {
  docker-compose -f "$DOCKER_COMPOSE_FILE" up -d || return 1
}

function prepare_log_dir {
  mkdir -p "$PROJECT_DIR/var/log"
}

function print_verbose {
    echo "$(date '+%Y-%m-%d %H:%M:%S') $1"
}

function parse_arguments {
for i in "$@"; do
  case $i in
    -f=*|--docker-file=*)
      DOCKER_COMPOSE_FILE="${i#*=}"
      shift
      ;;
    -h|--help)
      HELP="1"
      shift
      ;;
    -*|--*)
      echo "Unknown option $i"
      exit 1
      ;;
    *)
      ;;
  esac
done

set -- "${POSITIONAL_ARGS[@]}"
}

function print_help {
  echo 'Usage: '
  echo '  -h, --help        Print help'
  echo '  -f, --docker-file Pass docker file'
  exit 0
}

function parse_file_path {
    local PATH=""

    if [[ $1 =~ ^/.*$ ]]
    then
      PATH="$1"
    elif [[ $1 =~ ^~/.*$ ]]
    then
      PATH="$HOME/$(echo $1 | grep -oP '(?<=~\/).*')"
    else
      PATH="$PROJECT_DIR/$1"
    fi

    echo "$PATH"
}

function print_verbose_and_exit_wit_error {
  print_verbose $@
  exit 1
}

function start_pulling {
  if test -f "$FLAG_FILE";
  then
    prepare_log_dir
    print_verbose 'git pull' && pull_git 1>/dev/null && print_verbose 'git pulled'
    print_verbose 'stop docker' && stop_docker && print_verbose 'docker stopped' || print_verbose_and_exit_wit_error 'docker restart error'
    print_verbose 'start docker' && start_docker && print_verbose 'docker started' || print_verbose_and_exit_wit_error 'docker restart error'
  else
    echo "$FLAG_FILE does not exists"
    exit 1
  fi
}

parse_arguments "$@"

DOCKER_COMPOSE_FILE="$(parse_file_path $DOCKER_COMPOSE_FILE)"

if [ "$HELP" == "1" ]; then
  print_help
fi

if test -f "$DOCKER_COMPOSE_FILE";
then
  start_pulling "$@"
else
  echo 'Unable to find docker compose file'
  exit 1
fi
