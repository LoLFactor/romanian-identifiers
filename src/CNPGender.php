<?php

namespace LoLFactor\RomanianIdentifiers;

enum CNPGender
{
    case MALE;
    case FEMALE;

    /**
     * Returns the biological gender based on the first digit of the CNP, or null if the digits is not in the range 1-8.
     *
     * @param int $digit
     *
     * @return \LoLFactor\RomanianIdentifiers\CNPGender|null
     */
    public static function fromCNPDigit(int $digit): ?CNPGender
    {
        return match ($digit) {
            1, 3, 5, 7 => CNPGender::MALE,
            2, 4, 6, 8 => CNPGender::FEMALE,
            default => null,
        };
    }

    /**
     * Return the birth year based on the gender digit and birthYear parts of the CNP.
     * The gender digit is needed because the birthYear being only 2 digits, gender digits change for each century.
     * Returns null if the gender digit is outside the range 1-6.
     * 7 and 8 are for people with fiscal residency and do not correlate to a particular century.
     *
     * @param int    $genderDigit         First digit of the CNP. Should be in the range 1-6.
     * @param string $lastTwoDigitsOfYear Left-padded year in century (00 is the first year and 99 is the last year in the CNP century).
     *
     * @return int|null
     */
    public static function birthYearFromCNPDigit(int $genderDigit, string $lastTwoDigitsOfYear): ?int
    {
        return match ($genderDigit) {
            1, 2 => intval('19' . $lastTwoDigitsOfYear),
            3, 4 => intval('18' . $lastTwoDigitsOfYear),
            5, 6 => intval('20' . $lastTwoDigitsOfYear),
            default => null,
        };
    }
}
