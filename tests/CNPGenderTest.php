<?php

use LoLFactor\RomanianIdentifiers\CNPGender;

it('returns the gender based on the CNP gender digit', function (int $digit, ?CNPGender $gender) {
    expect(CNPGender::fromCNPDigit($digit))->toBe($gender);
})->with([
    [0, null],
    [1, CNPGender::MALE],
    [2, CNPGender::FEMALE],
    [3, CNPGender::MALE],
    [4, CNPGender::FEMALE],
    [5, CNPGender::MALE],
    [6, CNPGender::FEMALE],
    [7, CNPGender::MALE],
    [8, CNPGender::FEMALE],
    [9, null],
]);

it('returns the birth year based on the CNP gender digit and the last two digits of the year', function (int $digit, string $lastTwoDigitsOfYear, ?int $year) {
    expect(CNPGender::birthYearFromCNPDigit($digit, $lastTwoDigitsOfYear))->toBe($year);
})->with([
    [0, '00', null],
    [1, '91', 1991],
    [2, '89', 1989],
    [3, '76', 1876],
    [4, '41', 1841],
    [5, '01', 2001],
    [6, '05', 2005],
    [7, '00', null],
    [8, '00', null],
    [9, '00', null],
]);
