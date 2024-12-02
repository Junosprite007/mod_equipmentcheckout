INSERT INTO mdl_local_equipment_partnership (
        pickupid,
        liaisonid,
        name,
        active,
        streetaddress_mailing,
        city_mailing,
        state_mailing,
        country_mailing,
        zipcode_mailing,
        streetaddress_pickup,
        city_pickup,
        state_pickup,
        country_pickup,
        zipcode_pickup,
        name_billing,
        streetaddress_billing,
        city_billing,
        state_billing,
        country_billing,
        timecreated
    )
VALUES (
        NULL,
        NULL,
        'Grand Haven Area Public Schools (GHAPS)',
        1,
        '1415 Beechtree Street',
        'Grand Haven',
        'WA',
        'USA',
        '12345',
        '456 Pickup St',
        'Pickup City',
        'Pickup State',
        'Pickup Country',
        '67890',
        'Billing Name',
        '789 Billing St',
        'Billing City',
        'Billing State',
        'Billing Country',
        UNIX_TIMESTAMP()
    );