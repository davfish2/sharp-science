# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: settings.spec.mjs >> 12. discount block is off by default, and applies when enabled
- Location: tests/settings.spec.mjs:24:1

# Error details

```
Test timeout of 90000ms exceeded.
```

# Page snapshot

```yaml
- generic [active] [ref=f2e1]:
  - link "Skip to content" [ref=f2e2] [cursor=pointer]:
    - /url: "#main"
  - region "Store notices" [ref=f2e3]:
    - generic [ref=f2e4]:
      - generic [ref=f2e5]: Research Use Only. Not for human or animal use
      - generic [ref=f2e6]: Order now, dispatched Monday
      - generic [ref=f2e7]: Free Express shipping over $200
  - banner [ref=f2e8]:
    - generic [ref=f2e9]:
      - link "Sharp Science home" [ref=f2e10] [cursor=pointer]:
        - /url: http://localhost:8888/
        - img "Sharp Science" [ref=f2e11]
      - navigation "Main" [ref=f2e16]:
        - button "Shop" [ref=f2e17] [cursor=pointer]
        - link "Certificates" [ref=f2e20] [cursor=pointer]:
          - /url: http://localhost:8888/certificates/
        - link "About" [ref=f2e21] [cursor=pointer]:
          - /url: http://localhost:8888/about/
        - link "FAQ" [ref=f2e22] [cursor=pointer]:
          - /url: http://localhost:8888/faq/
        - link "Contact" [ref=f2e23] [cursor=pointer]:
          - /url: http://localhost:8888/contact/
      - generic [ref=f2e24]:
        - button "Search" [ref=f2e25] [cursor=pointer]
        - button "Open cart" [ref=f2e29] [cursor=pointer]
  - main [ref=f2e32]:
    - generic [ref=f2e34]:
      - paragraph [ref=f2e35]:
        - generic [ref=f2e36]: "404"
        - text: Not found
      - heading "This page is not on the shelf." [level=1] [ref=f2e37]
      - paragraph [ref=f2e38]: Try the range, or search by catalogue number.
      - generic [ref=f2e39]:
        - link "Shop the range" [ref=f2e40] [cursor=pointer]:
          - /url: http://localhost:8888/shop/
        - link "Home" [ref=f2e41] [cursor=pointer]:
          - /url: http://localhost:8888/
  - contentinfo [ref=f2e42]:
    - generic [ref=f2e43]:
      - generic [ref=f2e44]:
        - img "Sharp Science" [ref=f2e45]
        - paragraph [ref=f2e50]: Laboratory glassware, sample vials and instruments, supplied across Australia.
      - navigation "Shop" [ref=f2e51]:
        - heading "Shop" [level=2] [ref=f2e52]
        - list [ref=f2e53]:
          - listitem [ref=f2e54]:
            - link "Metabolic" [ref=f2e55] [cursor=pointer]:
              - /url: http://localhost:8888/shop/metabolic/
          - listitem [ref=f2e56]:
            - link "Recovery" [ref=f2e57] [cursor=pointer]:
              - /url: http://localhost:8888/shop/recovery/
          - listitem [ref=f2e58]:
            - link "Essentials" [ref=f2e59] [cursor=pointer]:
              - /url: http://localhost:8888/shop/essentials/
      - navigation "Information" [ref=f2e60]:
        - heading "Information" [level=2] [ref=f2e61]
        - list [ref=f2e62]:
          - listitem [ref=f2e63]:
            - link "Certificates" [ref=f2e64] [cursor=pointer]:
              - /url: http://localhost:8888/certificates/
          - listitem [ref=f2e65]:
            - link "Shipping" [ref=f2e66] [cursor=pointer]:
              - /url: http://localhost:8888/shipping/
          - listitem [ref=f2e67]:
            - link "Returns" [ref=f2e68] [cursor=pointer]:
              - /url: http://localhost:8888/returns/
          - listitem [ref=f2e69]:
            - link "Terms of Sale" [ref=f2e70] [cursor=pointer]:
              - /url: http://localhost:8888/terms-of-sale/
          - listitem [ref=f2e71]:
            - link "Privacy" [ref=f2e72] [cursor=pointer]:
              - /url: http://localhost:8888/privacy/
          - listitem [ref=f2e73]:
            - link "FAQ" [ref=f2e74] [cursor=pointer]:
              - /url: http://localhost:8888/faq/
      - generic [ref=f2e75]:
        - heading "Contact" [level=2] [ref=f2e76]
        - list [ref=f2e77]:
          - listitem [ref=f2e78]:
            - link "Contact us" [ref=f2e79] [cursor=pointer]:
              - /url: http://localhost:8888/contact/
    - generic [ref=f2e80]:
      - paragraph [ref=f2e81]: Research Use Only. Not for human or animal use
      - paragraph [ref=f2e82]: COA verified by Janoshik
      - paragraph [ref=f2e83]: © 2026 Sharp Science · Prices in AUD including GST
```