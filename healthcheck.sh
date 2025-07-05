quizify-api-rest/healthcheck.sh
#!/bin/sh

# Usage: ./healthcheck.sh --connect host:port [--timeout seconds]
# Example: ./healthcheck.sh --connect mysql:3306 --timeout 5

HOSTPORT=""
TIMEOUT=5

while [ "$#" -gt 0 ]; do
  case "$1" in
    --connect)
      HOSTPORT="$2"
      shift 2
      ;;
    --timeout)
      TIMEOUT="$2"
      shift 2
      ;;
    *)
      shift
      ;;
  esac
done

if [ -z "$HOSTPORT" ]; then
  echo "Missing --connect argument"
  exit 2
fi

nc -z -w $TIMEOUT $(echo $HOSTPORT | cut -d: -f1) $(echo $HOSTPORT | cut -d: -f2)