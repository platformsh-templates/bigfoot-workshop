#!/usr/bin/env bash
######################################################
# Some fleet maintenance demo scripts, using the CLI.
#
# Enables the following workflow on a given project:
#
# .
# └── main
#     ├── staging
#     |   └── new-feature
#     └── auto-updates
#
# Notes:
#   1. At this point, there is no 'cleanup' script post-demo.
#   2. Source this file before running any command.
#   3. Create a new organization with some templates in it.
#   4. Define source operations in them, if you plan to use them.
#   5. Get someone with admin access to enable source operations for your org.
#   6. See Comments at EOF for individual demo details.
######################################################
# Utility functions, used throughout examples.

# list_org_projects: Print list of projects operation will be applied to before starting.
#   $1: Organization, as it appears in console.platform.sh.
list_org_projects () {
    platform project:list -o $1 --columns="ID, Title"
}

# get_org_projects: Retrieve an array of project IDs for a given organization.
#   Note: Makes array variable PROJECTS available to subsequent scripts.
#   $1: Organization, as it appears in console.platform.sh.
get_org_projects () {
    PROJECTS_LIST=$(platform project:list -o $1 --pipe)
    PROJECTS=($PROJECTS_LIST)
}

# add_project_var: Add a project level environment variable.
#   Note: makes a boolean variable REDEPLOY available to subsequent scripts.
#   $1: Variable name.
#   $2: Variable value.
#   $3: Target project ID.
add_project_var () {
    unset REDEPLOY
    VAR_STATUS=$(platform project:curl -p $3 /variables/env:$1 | jq '.status')
    if [ "$VAR_STATUS" != "null" ]; then
        platform variable:create \
            --name $1 \
            --value $2 \
            --prefix env: \
            --project $3 \
            --level project \
            --json false \
            --sensitive false \
            --visible-build true \
            --visible-runtime true \
            -q

        REDEPLOY=true
    else
        echo "Variable $1 already exists. Skipping."
        REDEPLOY=false
    fi
}

# add_env_var: Add environment level environment variable.
#   Note: makes a boolean variable REDEPLOY available to subsequent scripts.
#   $1: Variable name.
#   $2: Variable value.
#   $3: Target project ID.
#   $4: Target environment ID.
add_env_var () {
    unset REDEPLOY
    VAR_STATUS=$(platform project:curl -p $3 /environments/$4/variables/env:$1 | jq '.status')
    if [ "$VAR_STATUS" != "null" ]; then
        platform variable:create \
            --name $1 \
            --value "$2" \
            --prefix env: \
            --project $3 \
            --environment $4 \
            --level environment \
            --json false \
            --sensitive false \
            --visible-build true \
            --visible-runtime true \
            --enabled true \
            --inheritable true \
            -q
    else
        echo "Variable $1 already exists. Skipping."
    fi
}

# redeploy_env: Redeploy an environment.
#   $1: Target project ID.
#   $2: Target environment ID.
redeploy_env () {
    echo "Redeploying environment $2 on project $1."
    platform redeploy --project $1 --environment $2 -y -q
}

# run_sourceop: Run a source operation on an environment.
#   $1: Source operation command name, as defined in .platform.app.yaml.
#   $2: Target project ID.
#   $3: Target environment ID.
run_sourceop () {
    platform source-operation:run $1 --project $2 --environment $3 -y -q --no-wait
}

# get_project_default_branch: Get the default branch of a project
#   Note: Makes variable DEFAULT_BRANCH available to subsequent scripts.
#   $1: Target project ID.
get_project_default_branch () {
    unset DEFAULT_BRANCH
    DEFAULT_BRANCH=$(platform project:info --project $1 default_branch)
}

# prepare_environment: Verify a target environment exists. If exists sync it to its parent, create otherwise.
#   $1: Target project ID.
#   $2: Target environment ID.
#   $3: (Optional) Parent environment ID.
prepare_environment () {
    PROJECT=$1
    ENVIRONMENT=$2
    PARENT=$3
    unset ENV_CHECK
    ENV_CHECK=$(platform project:curl -p $PROJECT /environments/$ENVIRONMENT | jq -r '.status')
    if [ "$ENV_CHECK" = error ]; then
        echo "Environment $ENVIRONMENT does not exist."

        if [ -n "$PARENT" ]; then
            echo "Creating environment $ENVIRONMENT as a child of $PARENT.";
        else
            get_project_default_branch $PROJECT
            PARENT=$DEFAULT_BRANCH
            echo "Creating environment $ENVIRONMENT as a child of the default branch, $PARENT.";
        fi
        platform environment:branch --project $PROJECT $ENVIRONMENT $PARENT -y -q --force
    fi

    if [ "$ENV_CHECK" = active ]; then
        echo "Environment $ENVIRONMENT already exists. Syncing to its parent."
        platform environment:sync code data --project $PROJECT --environment $ENVIRONMENT -y -q
    fi

    if [ "$ENV_CHECK" = inactive ]; then
        echo "Environment $ENVIRONMENT already exists, but is inactive. Activating and syncing to its parent."
        platform environment:activate --project $PROJECT --environment $ENVIRONMENT -y -q
        platform environment:sync code data --project $PROJECT --environment $ENVIRONMENT -y -q
    fi
}

######################################################
######################################################
# Example 1.

add_projectlevel_var () {
    list_org_projects $1
    get_org_projects $1
    TARGET_ENV=$2
    for PROJECT in "${PROJECTS[@]}"
    do
        printf "\nAdding variable to project $PROJECT:\n"
        # Ensure the target environment exists, is active, and up-to-date with its parent.
        prepare_environment $PROJECT $TARGET_ENV
        # Add the project-level environment variable.
        add_project_var SANITIZE_STAGING true $PROJECT

        if [ "$REDEPLOY" = true ]; then
            echo "Redeploying affected environments following added variable."
            # Get the default branch (production environment).
            get_project_default_branch $PROJECT
            # Redeploy the production environment.
            redeploy_env $PROJECT $DEFAULT_BRANCH
            # Redeploy the target staging environment.
            redeploy_env $PROJECT $TARGET_ENV
        else
            echo "Variable not added. Skipping redeploys."
        fi
    done
}

######################################################
######################################################
# Example 2.
# Adding a build-visible environment level variable SITE_NAME to
#   every project in an organization, so it can be used within
#   a dedicated new-feature environment to test that feature.
#   Also can be used/described as triggering a rebuild.
add_envlevel_var () {
    list_org_projects $1
    get_org_projects $1
    TARGET_ENV=$2
    PARENT_ENV=$3
    for PROJECT in "${PROJECTS[@]}"
    do
        printf "\nAdding variable to environment $TARGET_ENV on project $PROJECT:\n"
        # Ensure the parent environment exists, is active, and up-to-date with its parent.
        prepare_environment $PROJECT $PARENT_ENV
        # Ensure the target environment exists, is active, and up-to-date with its parent.
        prepare_environment $PROJECT $TARGET_ENV $PARENT_ENV

        # Add the project-level environment variable.
        add_env_var SITE_NAME "Symfony Sightings" $PROJECT $TARGET_ENV
    done
}

######################################################
######################################################
# Example 3.

run_update_so () {
    list_org_projects $1
    get_org_projects $1
    TARGET_ENV=$2
    OPERATION=$3
    for PROJECT in "${PROJECTS[@]}"
    do
        printf "\nRunning source operation $OPERATION on environment $TARGET_ENV on project $PROJECT:\n"
        # Ensure the parent environment exists, is active, and up-to-date with its parent.
        prepare_environment $PROJECT $TARGET_ENV

        # Run the source operation on a given environment.
        run_sourceop $OPERATION $PROJECT $TARGET_ENV

    done
}