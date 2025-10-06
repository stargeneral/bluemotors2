# Quick Reference - Blue Motors Southampton Plugin

## 🔗 Repository
**GitHub:** https://github.com/stargeneral/bluemotors2  
**Local Path:** `C:\Users\Peter\Local Sites\bluemotorsnew\app\public\wp-content\plugins\blue-motors-southampton`

## 🚨 URGENT: Security Actions Required

### Regenerate These Exposed API Keys IMMEDIATELY:

1. **Stripe Keys** → https://dashboard.stripe.com/apikeys
   - Generate NEW test keys
   - Delete old keys starting with `pk_test_51RMp23...` and `sk_test_51RMp23...`

2. **DVLA API Key** → https://developer-portal.driver-vehicle-licensing.api.gov.uk/
   - Generate NEW API key
   - Delete old key: `w2unkuUU9hap...`

3. **Update wp-config.php** with new keys:
```php
define('BM_STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_NEW_KEY');
define('BM_STRIPE_SECRET_KEY', 'sk_test_YOUR_NEW_KEY');
define('BM_DVLA_API_KEY', 'YOUR_NEW_KEY');
```

## 📋 Immediate Next Steps (Priority Order)

1. ✅ Regenerate API keys (see above)
2. ✅ Configure wp-config.php with new keys
3. ✅ Test booking flow end-to-end
4. ✅ Verify email notifications work
5. ✅ Add repository description on GitHub
6. ✅ Review and clean up archive/ folder

## 📚 Key Documentation Files

- **HANDOVER-DOCUMENT.md** - Complete project handover (READ THIS FIRST)
- **SECURITY-CONFIG.md** - API key configuration guide
- **README.md** - Plugin features and usage
- **CHANGELOG.md** - Version history

## 🎯 For Your Next Chat Session

Use this prompt to continue:

```
I'm continuing work on the Blue Motors Southampton WordPress plugin deployed to 
https://github.com/stargeneral/bluemotors2. 

The plugin is located at: C:\Users\Peter\Local Sites\bluemotorsnew\app\public\wp-content\plugins\blue-motors-southampton

Please read HANDOVER-DOCUMENT.md for full context. 

I need help with: [YOUR SPECIFIC TASK]
```

## 🛠️ Common Tasks

### Pull Latest Changes
```bash
cd "C:\Users\Peter\Local Sites\bluemotorsnew\app\public\wp-content\plugins\blue-motors-southampton"
git pull origin master
```

### Make Updates
```bash
# After making changes
git add .
git commit -m "Description of changes"
git push origin master
```

### Check Status
```bash
git status
git log --oneline
```

## ✅ What's Completed

- ✅ Plugin deployed to GitHub
- ✅ Security: Removed hardcoded API keys
- ✅ Created comprehensive documentation
- ✅ Clean git history
- ✅ Proper .gitignore configuration

## ⚠️ What Needs Attention

- ⚠️ Regenerate exposed API keys (CRITICAL)
- ⚠️ Configure wp-config.php
- ⚠️ Test with new keys
- ⚠️ Review archive folder for cleanup
- ⚠️ Add GitHub repository metadata

## 📞 Support

- **Repository Issues:** https://github.com/stargeneral/bluemotors2/issues
- **Business Contact:** 023 8000 0000
- **Email:** southampton@bluemotors.co.uk

---

**Last Updated:** October 6, 2025  
**Version:** 1.4.0  
**Status:** Deployed to GitHub - Security keys need regeneration
