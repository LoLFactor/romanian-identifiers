<?php

use LoLFactor\RomanianIdentifiers\CNP;
use LoLFactor\RomanianIdentifiers\CNPGender;

it('reports whether a CNP is valid', function (string $stringRepresentation, $valid) {
    $cnp = new CNP($stringRepresentation);

    expect($cnp->isValid())->toBe($valid);
})->with([
    ['1000101015662', true], // male born in 1900
    ['2000101016051', true], // female born in 1900
    ['3000101011441', true], // male born in 1800
    ['4000101016352', true], // female born in 1800
    ['5000101019624', true], // male born in 2000
    ['6000101015928', true], // female born in 2000
    ['7000101018466', true], // male fiscal resident born in 1800 or 1900 or 2000
    ['8000101011676', true], // female fiscal resident born in 1800 or 1900 or 2000
    ['100010101566', false], // incorrect length
    ['1000101015665', false], // incorrect check digit
]);

it('handles CNP gender', function () {
    $maleCNP = new CNP('5000101019624');

    expect($maleCNP->getGender())->toBe(CNPGender::MALE)
        ->and($maleCNP->isGender(CNPGender::MALE))->toBe(true);

    $femaleCNP = new CNP('6000101015928');

    expect($femaleCNP->getGender())->toBe(CNPGender::FEMALE)
        ->and($femaleCNP->isGender(CNPGender::FEMALE))->toBe(true);

    $invalidCNP = new CNP('100010101566');

    expect($invalidCNP->getGender())->toBe(null)
        ->and($invalidCNP->isGender(CNPGender::MALE))->toBe(false)
        ->and($invalidCNP->isGender(CNPGender::FEMALE))->toBe(false);
});

it('handles CNP birthdate', function () {
    $validCNP = new CNP('5000101019624');

    expect($validCNP->getBirthDate())->toBe('2000-01-01')
        ->and($validCNP->isBirthYear(2000))->toBe(true)
        ->and($validCNP->isBirthYear(1987))->toBe(false)
        ->and($validCNP->isBirthMonth(1))->toBe(true)
        ->and($validCNP->isBirthMonth(2))->toBe(false)
        ->and($validCNP->isBirthDay(1))->toBe(true)
        ->and($validCNP->isBirthDay(2))->toBe(false);

    $fiscalResidentCNP = new CNP('8000101011676');

    expect($fiscalResidentCNP->getBirthDate())->toBe(null)
        ->and($fiscalResidentCNP->isBirthYear(1800))->toBe(false)
        ->and($fiscalResidentCNP->isBirthYear(1900))->toBe(false)
        ->and($fiscalResidentCNP->isBirthYear(2000))->toBe(false)
        ->and($fiscalResidentCNP->isBirthYear(1987))->toBe(false)
        ->and($fiscalResidentCNP->isBirthMonth(1))->toBe(true)
        ->and($fiscalResidentCNP->isBirthMonth(2))->toBe(false)
        ->and($fiscalResidentCNP->isBirthDay(1))->toBe(true)
        ->and($fiscalResidentCNP->isBirthDay(2))->toBe(false);

    $invalidCNP = new CNP('100010101566');

    expect($invalidCNP->getBirthDate())->toBe(null)
        ->and($invalidCNP->isBirthYear(1900))->toBe(false)
        ->and($invalidCNP->isBirthMonth(1))->toBe(false)
        ->and($invalidCNP->isBirthDay(1))->toBe(false);
});
