<?php


namespace App\Validator\Constraints;


class NotOverlappingOpeningHoursValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $validator = Validation::createValidator();

        $errors = $validator->validate($value, [
            new Assert\All([
                'constraints' => new TimeRange(),
            ]),
        ]);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->context
                    ->buildViolation($error->getMessage())
                    ->atPath($error->getPropertyPath())
                    ->addViolation();
            }

            return;
        }

        try {

            $data = SchemaDotOrgParser::parseCollection($value);
            $data['overflow'] = true;

            $openingHours = OpeningHours::create($data);

        } catch (OverlappingTimeRanges $e) {

            $this->context
                ->buildViolation($e->getMessage())
                ->addViolation();
        }
    }
}