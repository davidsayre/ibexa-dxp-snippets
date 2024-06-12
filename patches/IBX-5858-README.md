https://support.ibexa.co/ticket/view/10996

A bug fix has been produced and successfully tested. You can find the provided patch in the attachment.

Before applying it, we advise to test the patch using the --dry-run parameter, executing the following command from the vendor/ibexa/elasticsearch directory:

    patch -p1 --dry-run -i IBX-5858.patch

We also advise you to make a backup copy of the following files before applying the patch (from the vendor/ibexa/elasticsearch directory):
src/bundle/Resources/config/services.yaml

If the test is successful and you got no "hunk FAILED" errors, apply the patch using the following command:

    patch -p1 -i IBX-5858.patch

You can always revert the patch using the following command:

    patch -p1 -R -i IBX-5858.patch

As an alternative to manually applying the patch, you can use composer patches plugin.

After applying the patch please remember that cache must be cleared. In addition to that, if the fix is related to frontend (CSS, JS tweaks) you also might need to regenerate assets.

Since the solution for the reported problem has been provided, I am hereby closing this ticket. Don't hesitate to reopen it if you stumble upon any difficulties with the patching process or have further questions to ask.

UPDATE: 2023-06-16 patch no longer needed after update