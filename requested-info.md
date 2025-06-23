Here is the full information from the provided PDF excerpts, formatted in Markdown:

```markdown
# Documentation for Al Baraka E-Payment Gateway

## Table of Contents

1.  Documentation for Al Baraka E-Payment Gateway
2.  Introduction
3.  About the Service
4.  Targeted Parties in this Documentation
5.  How the Service Works
6.  General Architecture
7.  Flowchart
8.  Ensuring Security of Exchanged Data
9.  Service Integration
10. Data Sent When Requesting the Service
11. Returned Data Upon Completion of the Operation
12. Data Sent for Acknowledging the Operation Result
13. Example of Service Integration
14. Example of Exchanged Data

## Introduction

### About the Service

Al Baraka Bank provides an electronic payment service through a payment gateway that can be used by all e-commerce websites and smartphone applications operating in the field of e-commerce. This facilitates the payment process in a secure and simple way.

### Targeted Parties in this Documentation

This documentation targets owners of websites and smartphone applications who wish to enable e-payment functionality on their sites and applications for specific goods or services. This documentation provides a detailed explanation of the steps for using the service and the information exchanged to complete the payment process correctly.

## How the Service Works

### General Architecture

### Flowchart

### Ensuring Security of Exchanged Data

To ensure the security and protection of data exchanged between the e-commerce website and the e-payment gateway, the following conditions must be met:

*   The merchant must declare the domain name and IP address that will be using the service, so that Al Baraka Bank can configure and grant access to the e-payment gateway.
*   All messages exchanged between the e-commerce website and the e-payment gateway must be encrypted using the TLS 1.2 protocol.

## Service Integration

### Data Sent When Requesting the Service

The following table contains the data that the e-commerce website must send to the e-payment gateway using the HTTPS POST method for the payment process to succeed. The data for these fields comes from different sources.

*   Al Baraka Bank provides the merchant with a configuration file (`Configuration File`) that includes merchant identification and authentication data, and the necessary data for the e-payment gateway to process the payment operation.
*   The merchant's e-commerce website provides transaction data (e.g., reference number, amount, currency), buyer data, date and time of the transaction, in addition to buyer browser data and the necessary links to complete the transaction.

| Data Name (Arabic) | Data Name (English) | Data Source          | Description / Requirement | Data Length & Type (Arabic) | Data Length & Type (English)              |
| :----------------- | :------------------ | :------------------- | :------------------------ | :-------------------------- | :---------------------------------------- |
| PSP ID             | pspId               | ملف التكوين (Config File) | Required                  | Length <= 8, Alphanumeric   | Length <= 8, Alphanumeric                 |
| MPI ID             | mpiId               | ملف التكوين (Config File) | Required                  | Length <= 8, Alphanumeric   | Length <= 8, Alphanumeric                 |
| معرف التاجر       | cardAcceptor        | ملف التكوين (Config File) | Required                  | Length <= 15, Alphanumeric  | Length <= 15, Alphanumeric                |
| رمز فئة التاجر   | mcc                 | ملف التكوين (Config File) | Required                  | Length = 4, Numeric         | Length = 4, Numeric                       |
| أدوات معرفة الدفع | merchantKitId       | ملف التكوين (Config File) | Required                  | Length <= 8, Alphanumeric   | Length <= 8, Alphanumeric                 |
| رمز المصداقية     | authenticationToken | ملف التكوين (Config File) | Required                  | Length <= 36, Alphanumeric  | Length <= 36, Alphanumeric                |
| العملة             | currency            | ملف التكوين (Config File) | Required                  | Length = 3, Alphanumeric (ISO 4217 alpha-3 Code) | Length = 3, Alphanumeric (ISO 4217 alpha-3 Code) |
| معرف نوع العملية | transactionTypeIndicator | ملف التكوين (Config File) | Must be "SS"              | "Must be “SS”"              | "Must be “SS”"                            |
| الرقم المرجعي للعملية | transactionReference | موقع التاجر (Back-end) | Required                  | Length <= 12, Alphanumeric  | Length <= 12, Alphanumeric                |
| اللغة              | language            | موقع التاجر (Back-end) | Required                  | "en” for English “ar” for Arabic" | "en” for English “ar” for Arabic"       |
| قيمة العملية      | transactionAmount   | موقع التاجر (Back-end) | Required                  | Length <= 12, Numeric "0.00" (use “.” for decimalization) | Length <= 12, Numeric "0.00" (use “.” for decimalization) |
| البريد الإلكتروني للمشتري | cardHolderMailAddress | موقع التاجر (صفحة الدفع) | Required                  | Length <= 30, Alphanumeric  | Length <= 30, Alphanumeric                |
| رقم هاتف المشتري | cardHolderPhoneNumber | موقع التاجر (صفحة الدفع) | Required                  | Length <= 14, Alphanumeric  | Length <= 14, Alphanumeric                |
| تاريخ ووقت التاجر | dateTimeSIC         | موقع التاجر (Back-end) | Optional                  | Length = 14, Numeric, Format YYYYMMddhhmmss | Length = 14, Numeric, Format YYYYMMddhhmmss |
| IP المشتري        | cardHolderIPAddress | موقع التاجر (متصفح الويب) | Required                  | Length <= 24, Alphanumeric  | Length <= 24, Alphanumeric                |
| بلد المشتري       | countryCode         | موقع التاجر (متصفح الويب) | Required                  | Length = 3, Alphanumeric (ISO 3166-1 alpha-3 Code) | Length = 3, Alphanumeric (ISO 3166-1 alpha-3 Code) |
| تاريخ ووقت المشتري | dateTimeBuyer       | موقع التاجر (متصفح الويب) | Optional                  | Length = 14, Numeric, Format YYYYMMddhhmmss | Length = 14, Numeric, Format YYYYMMddhhmmss |
| رابط الإرجاع      | redirectBackURL     | موقع التاجر (Back-end) | Required                  | The link to which the payment gateway will redirect the buyer upon completion of the payment process. | The link to which the payment gateway will redirect the buyer upon completion of the payment process. |
| رابط النتيجة      | callBackURL         | موقع التاجر (Back-end) | Required                  | The link to which the payment gateway will send the result of the operation. | The link to which the payment gateway will send the result of the operation. |

## Returned Data Upon Completion of the Operation

Upon successful completion of cardholder authentication and authorization, the payment gateway returns the result of the operation to the `callBackURL`, in addition to the transaction reference number for the merchant, as shown in the table below.

| Data Name (Arabic) | Data Name (English) | Data Receiver        | Description / Requirement | Data Length & Type (Arabic) | Data Length & Type (English) |
| :----------------- | :------------------ | :------------------- | :------------------------ | :-------------------------- | :--------------------------- |
| رقم المصادقة      | authorizationNumber | موقع التاجر (Back-end) | Required                  | Length = 6, Numeric         | Length = 6, Numeric          |
| رقم المتابعة والتدقيق | stan                | موقع التاجر (Back-end) | Required                  | Length = 6, Numeric         | Length = 6, Numeric          |
| الرقم المرجعي للعملية | idTransaction       | موقع التاجر (Back-end) | Required                  | Length <= 12, Alphanumeric  | Length <= 12, Alphanumeric   |
| حالة العملية      | transactionStat     | موقع التاجر (Back-end) | Required                  | "S” for Success “F” for Failed" | "S” for Success “F” for Failed" |

## Data Sent for Acknowledging the Operation Result (Acknowledgement)

The e-commerce website confirms receipt of the operation result from the payment gateway and acknowledges it by sending a response that includes "OK" or "KO" (depending on whether the acknowledgment fails). If the acknowledgment fails, the e-payment gateway cancels the payment operation. In all cases, upon completion, the payment gateway redirects the buyer to the link specified in "redirectBackURL" for the e-commerce website to display the operation result to the buyer.

| Data Name (Arabic) | Data Name (English) | Data Receiver        | Description / Requirement | Data Length & Type (Arabic) | Data Length & Type (English) |
| :----------------- | :------------------ | :------------------- | :------------------------ | :-------------------------- | :--------------------------- |
| الرد من موقع التاجر | responseCode        | موقع التاجر (Back-end) | Required                  | "OK” for Success “KO” for Failed" | "OK” for Success “KO” for Failed" |

## Example of Service Integration

This example is an HTML page that acts as an intermediary between the payment page on the e-commerce website and the data entry page for the e-payment gateway. The purpose of this page is to redirect the user and transfer data from the e-commerce website to the e-payment gateway.

```html
<html>
<head><title>Processing Payment...</title></head>
<body style="text-align: center;"
onLoad="document.forms['baraka_auto_form'].submit();">
<p style="text-align: center;">Please wait, your order is being
processed and you will be redirected to the baraka website.</p>
<form method="post" id="formProfs"
action="http://172.17.130.1:8080/ss-ecom-merchant-
kit/buyForm/completeTransaction" name="baraka_auto_form" enctype="application/x-
www-form-urlencoded">
<input type="text" hidden="true" id="pspId" value="PSP_001"
name="pspId">
<input type="text" hidden="true" id="mpiId" value="mpi-test"
name="mpiId">
<input type="text" hidden="true" id="cardAcceptor"
value="77777777" name="cardAcceptor">
<input type="text" hidden="true" id="mcc" value="4444"
name="mcc">
<input type="text" hidden="true" id="merchantKitId" value="mki-
test" name="merchantKitId">
<input type="text" hidden="true" id="authenticationToken"
value="BD43C384153FJ62EU950250568EE9ED" name="authenticationToken">
<input type="text" hidden="true" id="language" value="en"
name="language">
<input type="text" hidden="true" id="currency" value="SYP"
name="currency">
<input type="text" hidden="true" id="countryCode" value="SYR"
name="countryCode">
<input type="text" hidden="true" id="transactionTypeIndicator"
value="SS" name="transactionTypeIndicator">
<input type="text" hidden="true" id="transactionAmount"
value="3065.0" name="transactionAmount">
<input type="text" hidden="true" id="cardHolderMailAddress"
value="example@example.com" name="cardHolderMailAddress">
<input type="text" hidden="true" id="cardHolderPhoneNumber"
value="0999999999" name="cardHolderPhoneNumber">
<input type="text" hidden="true" id="cardHolderIPAddress"
value="196.12.213.90" name="cardHolderIPAddress">
<input type="text" hidden="true" id="transactionReference"
value="123456" name="transactionReference">
<input type="text" hidden="true" id="dateTimeBuyer"
value="20210222134953" name="dateTimeBuyer">
<input type="text" hidden="true" id="redirectBackUrl"
value="http://localhost/e-com/" name="redirectBackUrl">
<input type="text" hidden="true" id="callBackUrl"
value="http://localhost/e-com/Baraka/success" name="callBackUrl">
<p>
<input type="submit" name="pp_submit" value="Click here if
you&#039;re not automatically redirected..."  />
</p>
</form>
</body>
</html>
```

## Example of Exchanged Data

The previous example sends the following data:

*   `transactionReference`: `123456`
*   `pspId`: `PSP_001`
*   `mpiId`: `mpi-test`
*   `cardAcceptor`: `77777777`
*   `dateTimeBuyer`: `20210222134953`
*   `redirectBackUrl`: `http://localhost/e-com/`
*   `callBackUrl`: `http://localhost/e-com/Baraka/success`
*   `merchantKitId`: `mki-test`
*   `mcc`: `4444`
*   `authenticationToken`: `BD43C384153FJ62EU950250568EE9ED`
*   `transactionAmount`: `3065.0`
*   `cardHolderIPAddress`: `196.12.213.90`
*   `language`: `en`
*   `countryCode`: `SYR`
*   `cardHolderPhoneNumber`: `0999999999`
*   `cardHolderMailAddress`: `example@example.com`
*   `currency`: `SYP`
*   `transactionTypeIndicator`: `SS`

Upon successful completion of cardholder authentication and authorization, the e-payment gateway sends the following data to the `callBackURL` specified above:

```json
{"transactionStat":"S","idTransaction":"12345"}
```
The e-commerce website receives this response on the `callBackURL` and sends an acknowledgment response as follows:

```json
{"responseCode":"OK"}
```
