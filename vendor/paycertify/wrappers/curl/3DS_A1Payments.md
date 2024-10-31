## 3DS – PayCertify 3DS wrapper and A1Payments integration

### Authentication, Card Enrollment and Payment Authentication Request

The initial steps to use our 3DS platform integrating with A1Payments transactions are the following from our [3DS – Platform agnostic version](../master/curl/3DS.md):

1. [Authenticate](../master/curl/3DS.md#authentication)
2. [Check the card enrollment](../master/curl/3DS.md#check-card-enrollment)
3. [Payment Authentication Request (PAREQ)](../master/curl/3DS.md#payment-authentication-request-pareq)

After those steps, you'll receive the CAVV, ECI and XID parameters which are necessary to create the A1Payments transaction.

### Performing a sale in A1Payments with PayCertify 3DS

To create a transaction in A1Payments you'll have to provide the CAVV, ECI and XID fields to their
end filled with the same 3 values obtained in response from PayCertify's 3DS wrapper.


| PayCertify response field | A1Payments request field |
|---------------------------|--------------------------|
| `cavv` | `CAVV` |
| `eci`  | `ECI`  |
| `xid`  | `XID`  |

The fields in 3DS response and A1Payments request have the same name but in uppercase.

