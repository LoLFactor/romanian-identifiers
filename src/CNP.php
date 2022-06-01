<?php

namespace LoLFactor\RomanianIdentifiers;

class CNP
{
    /**
     * Verification key for computing the check digit.
     */
    protected final const VERIFICATION_KEY = '279146358279';

    /**
     * Regex pattern to match against and split the string representation of the CNP.
     */
    protected final const PATTERN = '/^(?P<genderDigit>\d)(?P<birthYear>\d\d)(?P<birthMonth>\d\d)(?P<birthDay>\d\d)\d{5}(?P<checkDigit>\d)$/';

    /**
     * @var int Determined from first digit of a CNP. Denotes biological gender (odd for males, even for females, between 1 and 8, depending on birth century).
     */
    protected int $genderDigit;

    /**
     * Determined from first three digits of the CNP. Second and third will be last two digits of the birth year, and the gender digit dictates the century.
     * Will be null if the gender digit is either 7 or 8 (reserved for people with fiscal residency; do not correlate to a particular century).
     *
     * @var int|null
     */
    protected ?int $birthYear;

    /**
     * @var int Determined from digits 4 and 5 of the CNP. Originally a left-padded month of year index (01 is January, 12 is December).
     */
    protected int $birthMonth;

    /**
     * @var int Determined from digits 6 and 7 of the CNP. Originally a left-padded day of month.
     */
    protected int $birthDay;

    /**
     * @var bool Whether this CNP is valid or not.
     */
    protected bool $isValid;

    /**
     * Creates a new CNP object which holds all the info about a particular CNP.
     *
     * @param string $stringRepresentation String representation of a CNP.
     */
    public function __construct(string $stringRepresentation)
    {
        $matchResult = preg_match(self::PATTERN, $stringRepresentation, $matches);

        if ($matchResult !== 1) {
            $this->isValid = false;
            return;
        }

        extract($matches);

        $this->genderDigit = intval($genderDigit);
        $this->birthYear = CNPGender::birthYearFromCNPDigit($this->genderDigit, $birthYear);
        $this->birthMonth = intval($birthMonth);
        $this->birthDay = intval($birthDay);

        $this->isValid = intval($checkDigit) === static::computeCheckDigit($stringRepresentation);
    }

    /**
     * Return whether this is a valid CNP.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Return the CNPGender determined from the CNP, or null if this CNP in invalid.
     *
     * @return \LoLFactor\RomanianIdentifiers\CNPGender|null
     */
    public function getGender(): ?CNPGender
    {
        if (!$this->isValid) {
            return null;
        }

        return CNPGender::fromCNPDigit($this->genderDigit);
    }

    /**
     * Return whether the determined CNP gender matches the supplied one.
     * Returns false for an invalid CNP.
     *
     * @param \LoLFactor\RomanianIdentifiers\CNPGender $gender The asserted gender.
     *
     * @return bool
     */
    public function isGender(CNPGender $gender): bool
    {
        if (!$this->isValid) {
            return false;
        }

        return $this->getGender() === $gender;
    }

    /**
     * Returns the ISO-formatted birthdate according to this CNP, if it's valid.
     * If the CNP is of a fiscal resident, it will also return null, since the exact date cannot be determined.
     *
     * @return string|null ISO-formatted birthdate according to this CNP or null.
     */
    public function getBirthDate(): ?string
    {
        if (!$this->isValid) {
            return null;
        }

        // Fiscal residency
        if ($this->birthYear === null) {
            return null;
        }

        $birthMonth = str_pad($this->birthMonth, 2, '0', STR_PAD_LEFT);
        $birthDay = str_pad($this->birthDay, 2, '0', STR_PAD_LEFT);

        return "{$this->birthYear}-{$birthMonth}-{$birthDay}";
    }

    /**
     * Returns whether the determined CNP birth year matches the supplied one.
     * Returns false for an invalid CNP.
     * If the CNP is of a fiscal resident, it will return false, since the birth year cannot be determined.
     *
     * @param int $year The asserted birth year.
     *
     * @return bool
     */
    public function isBirthYear(int $year): bool
    {
        if (!$this->isValid) {
            return false;
        }

        // Fiscal residency
        if ($this->birthYear === null) {
            return false;
        }

        return $this->birthYear === $year;
    }

    /**
     * Returns whether the determined CNP birth month matches the supplied one.
     * Returns false for an invalid CNP.
     *
     * @param int $month The asserted birth month.
     *
     * @return bool
     */
    public function isBirthMonth(int $month): bool
    {
        if (!$this->isValid) {
            return false;
        }

        return $this->birthMonth === $month;
    }

    /**
     * Returns whether the determined CNP birth day matches the supplied one.
     * Returns false for an invalid CNP.
     *
     * @param int $day The asserted birth day.
     *
     * @return bool
     */
    public function isBirthDay(int $day): bool
    {
        if (!$this->isValid) {
            return false;
        }

        return $this->birthDay === $day;
    }

    /**
     * Computes the check digit for a CNP.
     * CNPs are made up of 13 digits. The last one is a check digit which should be the check digits computed using the
     * first 12 digits.
     *
     * @param string $stringRepresentation String representation of a CNP.
     *
     * @return int The check digit.
     */
    protected static function computeCheckDigit(string $stringRepresentation): int
    {
        $index = 0;
        $total = 0;

        // Algorithm goes like this:
        // We go digits by digit up until the 12th one (inclusive)
        while ($index < 12) {
            // We get the CNP digit
            $cnpDigit = intval(substr($stringRepresentation, $index, 1));
            // We get the corresponding verification key digit
            $keyDigit = intval(substr(self::VERIFICATION_KEY, $index, 1));

            // We multiply them and add them to the total
            $total += $cnpDigit * $keyDigit;
            $index++;
        }

        // The remainder of dividing by 11 is the check digit
        $remainder = $total % 11;

        // If the remainder is 10, though, the check digit is 1
        return $remainder === 10 ? 1 : $remainder;
    }
}
