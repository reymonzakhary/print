#!/bin/sh

# input=$1
# watermark=$2
# output=$3
# stamp=$4
# abl=/tmp/all_but_last.pdf
# last=/tmp/last.pdf
# last_stamped=/tmp/last_stamped.pdf
#
# # detach
# pdftk $1 cat 1 output $abl
# pdftk $1 cat 2-r1 output $last
# #Stamp
# pdftk $abl $stamp $watermark output $last_stamped
# #Attach
# pdftk $last_stamped $last  cat output $output

input=$1
watermark=$2
output=$3
stamp=$4
page=$5

number_of_pages=$(pdftk $input dump_data | grep NumberOfPages | awk '{print $2}')

first=/tmp/$6_first.pdf
abl=/tmp/$6_all_but_last.pdf
last=/tmp/$6_last.pdf
last_stamped=/tmp/$6_last_stamped.pdf

first_split_index=$( expr $page - 1 )

last_split_index=$( expr $page + 1 )

if [ $number_of_pages -ne 1 ] && [ $page -eq 1 ];
    then
    # detach
    pdftk $1 cat $page output $abl
    pdftk $1 cat 2-r1 output $last
    #Stamp
    pdftk $abl $stamp $watermark output $last_stamped
    #Attach
    pdftk $last_stamped $last  cat output $output
elif [ $number_of_pages -eq 1 ] && [ $page -eq 1 ];
    then
    # detach
    pdftk $1 cat $page output $abl
    pdftk $1 cat output $last
    #Stamp
    pdftk $abl $stamp $watermark output $last_stamped
    #Attach
    pdftk $last_stamped cat output $output
elif [ $number_of_pages -eq $page ];
    then
    # detach
    pdftk $1 cat $page output $abl
    pdftk $1 cat 1-$first_split_index output $first
    #Stamp
    pdftk $abl $stamp $watermark output $last_stamped
    #Attach
    pdftk $first $last_stamped cat output $output
else
    # detach
    pdftk $1 cat 1-$first_split_index output $first
    pdftk $1 cat $page output $abl
    pdftk $1 cat $last_split_index-$number_of_pages output $last

    # stamp
    pdftk $abl $stamp $watermark output $last_stamped

    #attach
    pdftk $first $last_stamped $last cat output $output
fi

chmod a=rw $output
