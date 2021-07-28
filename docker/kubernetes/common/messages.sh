#!/bin/bash
source "./kubernetes/common/color.sh"

function error() {
    echo " "
    echo "$RED ERROR - $NC $1"
    echo " "
}

function info() {
    echo " "
    echo "$BLUE INFO - $NC $1"
    echo " "
}

function success() {
    echo " "
    echo "$GREEN OK - $NC $1"
    echo " "
}

function step() {
    echo " "  
    echo "$YELLOW $1 $NC"
    echo " "
}