#!/bin/bash

set -o errexit
set -o nounset
set -o pipefail
# set -o xtrace # Uncomment this line for debugging purposes

# Load libraries
. /opt/bitnami/scripts/libbitnami.sh
. /opt/bitnami/scripts/liblog.sh
. /opt/bitnami/scripts/libwebserver.sh

info "** Running init-plugins **"

# Add plugins that need to be activated at pod creation time here.
# NOTE: It's ok to activate a plugin that's previously been activated.
#wp plugin activate rapunzel
#wp plugin activate s3-uploads