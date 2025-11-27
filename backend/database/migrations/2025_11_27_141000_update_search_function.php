<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS "SearchContent"(text, text, text);');
        DB::unprepared('DROP FUNCTION IF EXISTS sp_search_content(text, text, text);');

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION sp_search_content(
    searchTerm text,
    filterType text DEFAULT NULL,
    subFilter  text DEFAULT NULL
)
RETURNS TABLE (
    "ID" int,
    "Title" varchar,
    "Description" varchar,
    "Mode" varchar,
    "Schedule" timestamp without time zone,
    "Location" varchar,
    "TrainingLink" varchar,
    "OrganizationName" varchar,
    "Qualifications" varchar,
    "Requirements" varchar,
    "ApplicationLetterAddress" varchar,
    "DeadlineofSubmission" date,
    "Type" varchar
)
LANGUAGE plpgsql
AS $function$
DECLARE
    v_likeTerm   text := '%' || COALESCE(searchTerm, '') || '%';
    v_filterType text := lower(trim(COALESCE(filterType, 'all')));
    v_subFilter  text := trim(COALESCE(subFilter, ''));
BEGIN
    IF v_filterType = 'training' THEN
        RETURN QUERY
        SELECT DISTINCT
            t."trainingID"::int                                              AS "ID",
            t.title::varchar                                                 AS "Title",
            t.description::varchar                                           AS "Description",
            ts.mode::varchar                                                 AS "Mode",
            ts.schedule::timestamp without time zone                         AS "Schedule",
            ts.location::varchar                                             AS "Location",
            ts."trainingLink"::varchar                                       AS "TrainingLink",
            o.name::varchar                                                  AS "OrganizationName",
            NULL::varchar                                                    AS "Qualifications",
            NULL::varchar                                                    AS "Requirements",
            NULL::varchar                                                    AS "ApplicationLetterAddress",
            NULL::date                                                       AS "DeadlineofSubmission",
            'Training'::varchar                                              AS "Type"
        FROM training t
        JOIN organization o   ON t."organizationID" = o."organizationID"
        JOIN trainingschedule ts ON t."trainingID" = ts."trainingID"
        WHERE (t.title ILIKE v_likeTerm OR t.description ILIKE v_likeTerm OR o.name ILIKE v_likeTerm)
          AND (v_subFilter = '' OR ts.mode ILIKE v_subFilter);

    ELSIF v_filterType = 'career' THEN
        RETURN QUERY
        SELECT
            c."careerID"::int                                                AS "ID",
            c.position::varchar                                              AS "Title",
            c.details::varchar                                               AS "Description",
            NULL::varchar                                                    AS "Mode",
            NULL::timestamp                                                  AS "Schedule",
            c."placeOfAssignment"::varchar                                   AS "Location",
            NULL::varchar                                                    AS "TrainingLink",
            o.name::varchar                                                  AS "OrganizationName",
            c."qualificationStandard"::varchar                               AS "Qualifications",
            NULL::varchar                                                    AS "Requirements",
            NULL::varchar                                                    AS "ApplicationLetterAddress",
            c."closingDate"::date                                            AS "DeadlineofSubmission",
            'Career'::varchar                                                AS "Type"
        FROM career c
        JOIN organization o ON c."organizationID" = o."organizationID"
        WHERE (c.position ILIKE v_likeTerm OR c.details ILIKE v_likeTerm OR o.name ILIKE v_likeTerm);

    ELSIF v_filterType = 'organization' THEN
        RETURN QUERY
        SELECT
            o."organizationID"::int                                          AS "ID",
            o.name::varchar                                                  AS "Title",
            o.location::varchar                                              AS "Description",
            NULL::varchar                                                    AS "Mode",
            NULL::timestamp                                                  AS "Schedule",
            o.location::varchar                                              AS "Location",
            o."websiteURL"::varchar                                          AS "TrainingLink",
            o.name::varchar                                                  AS "OrganizationName",
            NULL::varchar                                                    AS "Qualifications",
            NULL::varchar                                                    AS "Requirements",
            NULL::varchar                                                    AS "ApplicationLetterAddress",
            NULL::date                                                       AS "DeadlineofSubmission",
            'Organization'::varchar                                          AS "Type"
        FROM organization o
        WHERE (o.name ILIKE v_likeTerm OR o.location ILIKE v_likeTerm);

    ELSE
        RETURN QUERY
        SELECT DISTINCT
            t."trainingID"::int                                              AS "ID",
            t.title::varchar                                                 AS "Title",
            t.description::varchar                                           AS "Description",
            ts.mode::varchar                                                 AS "Mode",
            ts.schedule::timestamp without time zone                         AS "Schedule",
            ts.location::varchar                                             AS "Location",
            ts."trainingLink"::varchar                                       AS "TrainingLink",
            o.name::varchar                                                  AS "OrganizationName",
            NULL::varchar                                                    AS "Qualifications",
            NULL::varchar                                                    AS "Requirements",
            NULL::varchar                                                    AS "ApplicationLetterAddress",
            NULL::date                                                       AS "DeadlineofSubmission",
            'Training'::varchar                                              AS "Type"
        FROM training t
        JOIN organization o   ON t."organizationID" = o."organizationID"
        JOIN trainingschedule ts ON t."trainingID" = ts."trainingID"
        WHERE (t.title ILIKE v_likeTerm OR t.description ILIKE v_likeTerm OR o.name ILIKE v_likeTerm)

        UNION

        SELECT
            c."careerID"::int                                                AS "ID",
            c.position::varchar                                              AS "Title",
            c.details::varchar                                               AS "Description",
            NULL::varchar                                                    AS "Mode",
            NULL::timestamp                                                  AS "Schedule",
            c."placeOfAssignment"::varchar                                   AS "Location",
            NULL::varchar                                                    AS "TrainingLink",
            o.name::varchar                                                  AS "OrganizationName",
            c."qualificationStandard"::varchar                               AS "Qualifications",
            NULL::varchar                                                    AS "Requirements",
            NULL::varchar                                                    AS "ApplicationLetterAddress",
            c."closingDate"::date                                            AS "DeadlineofSubmission",
            'Career'::varchar                                                AS "Type"
        FROM career c
        JOIN organization o ON c."organizationID" = o."organizationID"
        WHERE (c.position ILIKE v_likeTerm OR c.details ILIKE v_likeTerm OR o.name ILIKE v_likeTerm)

        UNION

        SELECT
            o."organizationID"::int                                          AS "ID",
            o.name::varchar                                                  AS "Title",
            o.location::varchar                                              AS "Description",
            NULL::varchar                                                    AS "Mode",
            NULL::timestamp                                                  AS "Schedule",
            o.location::varchar                                              AS "Location",
            o."websiteURL"::varchar                                          AS "TrainingLink",
            o.name::varchar                                                  AS "OrganizationName",
            NULL::varchar                                                    AS "Qualifications",
            NULL::varchar                                                    AS "Requirements",
            NULL::varchar                                                    AS "ApplicationLetterAddress",
            NULL::date                                                       AS "DeadlineofSubmission",
            'Organization'::varchar                                          AS "Type"
        FROM organization o
        WHERE (o.name ILIKE v_likeTerm OR o.location ILIKE v_likeTerm);
    END IF;
END;
$function$;
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS sp_search_content(text, text, text);');
    }
};

