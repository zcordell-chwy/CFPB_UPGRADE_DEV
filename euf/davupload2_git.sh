#!/bin/bash

######################################################################
# davupload.sh
#
# This script uploads added or modified files from a git
# working copy to a RightNow CP site via webdav. 
#
# Author: cordell
# Date: 2014-10-23
#
# Requirements:
#
# 1) This script must exist in the 'euf' or 'customer' directory of your working
#    copy so the local path names relate to the server's path names.
#
# 2) The 'git' and 'cadaver' commands must be in your path.
#
# 3) A ~/.netrc file must exist with the login and password of a
#    valid staff acct.  The 'cadaver' command needs this to
#    log in.  Sample entry for the  ~/.netrc file:
#
#    machine somesite.custhelp.com
#    login somestaffacctlogin
#    password somestaffacctpasswd
#
# 4) VERY IMPORTANT!  Change the SITEDOMAIN to the site you are
#    working with!
#
SITEDOMAIN="cfpbfi--upgrade--upgdev.custhelp.com"
######################################################################



# NO USER-SERVICABLE PARTS INSIDE ####################################
BADFILES="0"
CHECKBADFILES="1"
BADFILESTMPFILE="/tmp/badfiles.txt"


if [ "$CHECKBADFILES" -eq "1" ]; then
	echo "Checking files..."
	grep -iRl --exclude="davupload.sh" "RightNow Fatal Error" * | grep -v ".git" >${BADFILESTMPFILE}
	BADFILES=`cat ${BADFILESTMPFILE} | wc -l`
	BADFILES=$((BADFILES - 1))
fi

if [ "$CHECKBADFILES" -eq "1" ] && [ "$BADFILES" -gt 0 ]; then

	echo "-- $BADFILES CORRUPT FILES FOUND IN YOUR WORKING COPY.  UNABLE TO UPLOAD TO WEBDAV. -------"
	cat ${BADFILESTMPFILE}
	echo "-- $BADFILES CORRUPT FILES FOUND IN YOUR WORKING COPY.  UNABLE TO UPLOAD TO WEBDAV. -------"

else
	echo "Files OK, uploading..."
	SCRIPTPATH="$(cd "${0%/*}" 2>/dev/null; echo "$PWD"/"${0##*/}")"
	DIRPATH=`dirname $SCRIPTPATH`
	CADAVERPATH=`which cadaver 2>/dev/null`
	GITPATH=`which git 2>/dev/null`
	TMPFILE="/tmp/davupload2.txt"
	
	cd $DIRPATH
	
	# Check to make sure this script is in the correct directory.
	if [ ! -d "development" ]; then
		echo "$0 - ERROR: This script must be in the 'customer' directory of your working copy."
		exit
	fi
	
	# Check to see if the 'git' command is available.
	if [ ! -f "${GITPATH}" ]; then
		echo "$0 - ERROR: The 'git' command does not appear to be in your path."
		exit
	fi
	
	# Check to see if the 'cadaver' command is available.
	if [ ! -f "${CADAVERPATH}" ]; then
		echo "$0 - ERROR: The 'cadaver' command does not appear to be in your path."
		exit
	fi
	
	# Run the 'git' command, check for Added or Modified files in the working copy, write
	# the results to the tmp file.
	# have to do the second line because git status outputs append's without a space before and
	# i suck at regular expressions.

	${GITPATH} status -s | grep '^\s[MA].*' | sed 's/ [MA] /mput /' >${TMPFILE}
	${GITPATH} status -s | grep '^AM.*' | sed 's/AM /mput /' >>${TMPFILE}
	${GITPATH} status -s | grep '^A\s.*' | sed 's/A /mput/' >>${TMPFILE}
	
	if [ -f "${TMPFILE}" ]; then
	
		# Run the 'cadaver' command, upload files from the tmp file
		${CADAVERPATH} -t https://${SITEDOMAIN}/dav/euf <${TMPFILE}
	
		# remove the tmp file
		# rm ${TMPFILE}
	else
		echo "$0 - ERROR: Unable to redirect output of 'git' command to ${TMPFILE}"
	fi

fi

if [ ! -f "${GITPATH}" ]; then
	rm ${BADFILESTMPFILE}
fi

######################################################################


