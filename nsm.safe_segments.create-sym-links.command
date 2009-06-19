#!/bin/bash

# This script creates symlinks from the local GIT repo into your EE install. It also copies some of the extension icons.

dirname=`dirname "$0"`

echo ""
echo "You are about to create symlinks for NSM Safe Segments"
echo "------------------------------------------------------"
echo ""
echo "Enter the full path to your ExpressionEngine install without a trailing slash [ENTER]:"
read ee_path
echo "Enter your ee system folder name [ENTER]:"
read ee_system_folder

ln -s "$dirname"/system/extensions/ext.nsm_safe_segments_ext.php "$ee_path"/"$ee_system_folder"/extensions/ext.nsm_safe_segments_ext.php
ln -s "$dirname"/system/language/english/lang.nsm_safe_segments_ext.php "$ee_path"/"$ee_system_folder"/language/english/lang.nsm_safe_segments_ext.php
