# ⚖️ Avocato - نظام إدارة مكاتب المحاماة

نظام متكامل (API) لإدارة مكاتب المحاماة، مبني على **Laravel 13**، يتيح إدارة القضايا والعملاء والمحامين والجلسات والمستندات والمدفوعات والأحكام مع صلاحيات وصول متعددة.

---

## 📋 فهرس المحتويات

1. [نظرة عامة عن المشروع](#-نظرة-عامة-عن-المشروع)
2. [التقنيات المستخدمة](#-التقنيات-المستخدمة)
3. [هيكلية المشروع](#-هيكلية-المشروع)
4. [قاعدة البيانات والنماذج (Models)](#-قاعدة-البيانات-والنماذج-models)
5. [حالة الميزات (Completed / In Progress / Not Started)](#-حالة-الميزات-completed--in-progress--not-started)
6. [نظام الصلاحيات (Roles & Permissions)](#-نظام-الصلاحيات-roles--permissions)
7. [جميع نقاط API](#-جميع-نقاط-api)
8. [أنماط البرمجة المستخدمة (Patterns)](#-أنماط-البرمجة-المستخدمة-patterns)
9. [كيفية تشغيل المشروع](#-كيفية-تشغيل-المشروع)
10. [قاعدة البيانات](#-قاعدة-البيانات)
11. [التغطية الاختبارية (Testing)](#-التغطية-الاختبارية-testing)
12. [نقاط القوة والضعف](#-نقاط-القوة-والضعف)
13. [خطة التطوير المستقبلية](#-خطة-التطوير-المستقبلية)

---

## 🧭 نظرة عامة عن المشروع

**Avocato** (مشتقة من كلمة "Avocat" الفرنسية والتي تعني محامي) هو **API Backend** خالص (لأغراض تصدير البيانات لتطبيق واجهة أمامية مستقلة - Mobile App / SPA).

### الأدوار الأساسية في النظام:
| الدور | الرمز | الوصف |
|-------|-------|-------|
| **Admin** | `admin` | مدير النظام - صلاحية كاملة |
| **Avocato** | `avocato` | محامٍ - يمكنه إدارة القضايا والجلسات |
| **Client** | `client` | عميل - يمكنه متابعة قضاياه |

### المهام الرئيسية للنظام:
- تسجيل وإدارة المستخدمين (محامون، عملاء)
- إنشاء وإدارة القضايا (Cases) مع تعيين الأطراف والمحامين
- إدارة جلسات المحكمة لكل قضية
- رفع وإدارة المستندات والملفات
- تسجيل الأحكام القضائية
- إدارة المدفوعات والرسوم
- نظام صلاحيات متكامل

---

## 🛠️ التقنيات المستخدمة

| التقنية | الإصدار | الاستخدام |
|---------|---------|-----------|
| **Laravel** | 13.x | إطار العمل الأساسي |
| **PHP** | ^8.3 | لغة البرمجة |
| **Laravel Sanctum** | ^5.x | المصادقة عبر API Tokens |
| **Spatie Laravel Permission** | ^7.3 | نظام الصلاحيات والأدوار |
| **SQLite** | - | قاعدة البيانات الافتراضية (قابل للتبديل) |
| **Pest PHP** | ^4.x | إطار الاختبارات |
| **Tailwind CSS** | v4 | تصميم الواجهات (للصفحات الافتراضية) |
| **Vite** | ^8.x | بناء الأصول (Assets) |

---

## 📁 هيكلية المشروع

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php          # ⚡ المصادقة (تسجيل/دخول/خروج)
│   │   │   ├── CaseController.php          # ⚡ إدارة القضايا (CRUD)
│   │   │   ├── CaseSessionController.php   # ⚡ إدارة جلسات القضايا (CRUD)
│   │   │   ├── ClientController.php        # ⚡ إدارة العملاء (CRUD كامل + overview)
│   │   │   ├── LawyerController.php        # ⚡ إدارة المحامين (CRUD كامل)
│   │   │   ├── LegalController.php         # ⚡ إدارة القوانين (CRUD كامل)
│   │   │   ├── LawyerDocumentController.php # ⚡ شهادات وملفات المحامي (CRUD كامل مع رفع ملفات)
│   │   │   ├── WarningHistoryController.php # ⚡ إنذارات المحامي (CRUD كامل مع ID تلقائي)
│   │   │   └── CaseController_.php         # ❌ ملف قديم - غير مستخدم
│   │   └── Controller.php                  # الـ Base Controller
│   └── Requests/
│       └── StoreCaseRequest.php            # ✅ قواعد التحقق الوحيدة (لإنشاء القضايا)
├── Models/
│   ├── User.php                            # نموذج المستخدم (مع صلاحيات Sanctum)
│   ├── CaseModel.php                       # نموذج القضية
│   ├── CaseParty.php                       # أطراف القضية
│   ├── CaseSession.php                     # جلسات القضية
│   ├── Document.php                        # المستندات
│   ├── Judgment.php                        # الأحكام القضائية
│   ├── Payment.php                         # المدفوعات
│   ├── Legal.php                           # القوانين (Legal)
│   ├── LawyerDocument.php                  # شهادات ومستندات المحامي
│   └── WarningHistory.php                  # إنذارات المحامي
├── Providers/
│   └── AppServiceProvider.php
└── Traits/
    ├── ApiResponse.php                     # ✅ توحيد شكل الاستجابات JSON
    └── HandleDocuments.php                 # ✅ رفع/استبدال/حذف المستندات

bootstrap/
├── app.php                                 # ⚙️ تسجيل Middleware (role, auth)

config/
├── permission.php                          # إعدادات Spatie Permission
├── sanctum.php                             # إعدادات Sanctum
└── ...

database/
├── migrations/                             # 16 ملف هجرة (جميع الجداول)
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── AdminUserSeeder.php                 # ✅ بذور المستخدمين والأدوار
│   ├── PermissionSeeder.php                # ✅ بذور الصلاحيات (يحتاج مراجعة)
│   └── LegalSeeder.php                     # ✅ بذور القوانين الافتراضية

routes/
├── api.php                                 # ✅ جميع مسارات API
├── web.php                                 # صفحة Laravel الافتراضية
└── console.php                             # أوامر Artisan

tests/
├── Feature/ExampleTest.php                 # ❗ اختبار تجريبي فقط
├── Unit/ExampleTest.php                    # ❗ اختبار تجريبي فقط
├── Pest.php
└── TestCase.php
```

---

## 🗃️ قاعدة البيانات والنماذج (Models)

النظام يحتوي على **10 نماذج** و **16 جدولاً** في قاعدة البيانات:

### 1. User (المستخدمون) - `users`
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | المفتاح الأساسي |
| name | string | الاسم |
| email | string | البريد الإلكتروني (unique) |
| mobile | string | رقم الجوال |
| image | string | صورة المستخدم |
| type | string | نوع المستخدم |
| is_active | boolean | نشط/غير نشط |
| status | string | الحالة |
| rate | integer | التقييم (يُعطى عشوائياً عند إنشاء محامٍ جديد) |
| bar_association_number | string | رقم نقابة المحامين (خاص بالمحامي) |
| office_location | string | موقع المكتب (خاص بالمحامي) |
| years_of_experience | integer | سنوات الخبرة (خاص بالمحامي) |
| specialty | string | التخصص (خاص بالمحامي) |
| bio | text | السيرة الذاتية (للمحامي والعميل) |

**العلاقات:**
- `casesCreated()` - القضايا التي أنشأها
- `casesAsLawyer()` - القضايا الموكلة إليه كمحامٍ (BelongsToMany عبر `case_lawyers`)
- `caseParticipations()` - مشاركاته كطرف في قضايا

### 2. CaseModel (القضايا) - `cases`
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | المفتاح الأساسي |
| case_number | string | رقم القضية (unique) |
| title | string | عنوان القضية |
| description | text | وصف القضية |
| type | string | نوع القضية |
| status | string | الحالة (pending/active/suspended/flagged/closed) - default: pending |
| court_name | string | اسم المحكمة |
| start_date | date | تاريخ البدء |
| created_by | FK | منشئ القضية (مستخدم) |

**العلاقات:**
- `creator()` - منشئ القضية
- `parties()` - أطراف القضية
- `lawyers()` - المحامون المكلفون
- `sessions()` - جلسات القضية
- `documents()` - مستندات القضية
- `judgments()` - أحكام القضية
- `payments()` - مدفوعات القضية

### 3. CaseParty (أطراف القضية) - `case_parties`
| الحقل | النوع | الوصف |
|-------|------|-------|
| case_id | FK | معرف القضية |
| user_id | FK | معرف المستخدم |
| role_in_case | enum | الدور (plaintiff/defendant/witness) |

### 4. CaseSession (جلسات القضية) - `case_sessions`
| الحقل | النوع | الوصف |
|-------|------|-------|
| case_id | FK | معرف القضية |
| session_date | datetime | تاريخ الجلسة |
| decision | text | القرار |
| notes | text | ملاحظات |
| next_session_date | datetime | تاريخ الجلسة القادمة |

### 5. Document (المستندات) - `documents`
| الحقل | النوع | الوصف |
|-------|------|-------|
| case_id | FK | معرف القضية |
| case_session_id | FK (nullable) | معرف الجلسة (اختياري) |
| uploaded_by | FK | رافع المستند (مستخدم) |
| file_path | string | مسار الملف |
| type | string | نوع المستند |
| title | string | عنوان المستند |

### 6. Judgment (الأحكام) - `judgments`
| الحقل | النوع | الوصف |
|-------|------|-------|
| case_id | FK | معرف القضية |
| judgment_date | date | تاريخ الحكم |
| content | longText | نص الحكم |
| is_final | boolean | نهائي/غير نهائي |

### 7. Payment (المدفوعات) - `payments`
| الحقل | النوع | الوصف |
|-------|------|-------|
| case_id | FK | معرف القضية |
| user_id | FK | معرف المستخدم |
| amount | decimal(10,2) | المبلغ |
| type | string | نوع الدفعة |
| status | enum | الحالة (pending/completed) |

### 8. Legal (القوانين) - `legals`
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | المفتاح الأساسي |
| name | string | اسم القانون |
| rule_number | string | رقم القاعدة (unique) |
| rule_description | text | وصف القاعدة |

### 9. LawyerDocument (ملفات المحامي) - `lawyer_documents`
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | المفتاح الأساسي |
| user_id | FK | معرف المحامي (users) |
| title | string | عنوان الملف/الشهادة |
| type | string | النوع (شهادة، رخصة، ...) |
| file_path | string | مسار الملف |
| description | text | وصف |

### 10. WarningHistory (إنذارات المحامي) - `warning_histories`
| الحقل | النوع | الوصف |
|-------|------|-------|
| id | bigint | المفتاح الأساسي |
| lawyer_id | FK `users` | المحامي الموجه إليه الإنذار |
| warning_id | string | رقم الإنذار الفريد (auto: WRN-0001) |
| reason | text | سبب الإنذار |
| sent_by | FK `users` | من أصدر الإنذار |
| date | date | تاريخ الإنذار |
| status | string | الحالة (pending/active/resolved) |

---

## 📊 حالة الميزات (Completed / In Progress / Not Started)

### ✅ الميزات المكتملة بالكامل

| الميزة | الوصف | الحالة |
|--------|-------|--------|
| **المصادقة** (Authentication) | تسجيل، دخول، خروج، جلب بيانات المستخدم عبر Sanctum Tokens | ✅ مكتمل |
| **نظام الصلاحيات** (RBAC) | 3 أدوار (admin, avocato, client) مع Middleware | ✅ مكتمل |
| **إدارة القضايا** | CRUD كامل مع Soft Delete وعرض ملخص (Overview) | ✅ مكتمل |
| **لوحة الإحصائيات** | `GET /api/cases-overview` - عدد القضايا حسب الحالة | ✅ مكتمل |
| **جلسات القضايا** | CRUD كامل مع دعم رفع المستندات أثناء الإنشاء | ✅ مكتمل |
| **إدارة المحامين** | CRUD كامل + تفعيل/تعطيل + جلب قضايا المحامي | ✅ مكتمل |
| **رفع المستندات** | عن طريق HandleDocuments Trait مع عمليات آمنة | ✅ مكتمل |
| **هيكلة قاعدة البيانات** | 13 جدول مع علاقات ومفاتيح أجنبية كاملة | ✅ مكتمل |
| **بذور البيانات** (Seeders) | إنشاء المستخدمين الافتراضيين والأدوار والصلاحيات والقوانين | ✅ مكتمل |
| **شكل الاستجابات** (Response Format) | تنسيق JSON موحد عبر ApiResponse Trait | ✅ مكتمل |
| **إدارة القوانين** (Legal) | CRUD كامل مع ApiResponse | ✅ مكتمل |
| **رفع ملفات مع إنشاء القضية** | إمكانية رفع مستندات اختيارية أثناء إنشاء القضية | ✅ مكتمل |
| **ربط المحامين بالقضية** | ربط المحامين (lawyers) تلقائياً بجدول `case_lawyers` عند إنشاء القضية، مع Auto-attach لمنشئ القضية إذا كان Avocato | ✅ مكتمل |
| **جلب قضايا المحامي** | `GET /api/lawyers/{id}/cases` يعمل الآن بشكل صحيح مع Pagination والبيانات المرتبطة | ✅ مكتمل |
| **إنشاء قضية ذكي حسب الدور** | Avocato يرسل `client_id` فقط، Client يرسل `lawyer_id` فقط، Admin يتحكم كاملاً - مع Auto-attach ذكي | ✅ مكتمل |
| **حقول المحامي المتقدمة** | إضافة Bar Association Number، Office Location، Years of Experience، Specialty و rate عشوائي | ✅ مكتمل |
| **إصلاح LawyerController** | Pagination، Validation موحّد مع `Rule::unique`، Response موحد | ✅ مكتمل |
| **حالات قضايا ثابتة** | 5 حالات: pending (افتراضي)، active، suspended، flagged، closed | ✅ مكتمل |
| **Lawyer Overview** | `GET /api/lawyers/overview` - إحصائيات المحامي | ✅ مكتمل |
| **ملفات المحامي (Lawyer Documents)** | Model + Migration + Controller + CRUD مع رفع ملفات وصور | ✅ مكتمل |
| **إنذارات المحامي (Warning History)** | Model + Migration + Controller + CRUD مع توليد ID تلقائي (WRN-xxxx) | ✅ مكتمل |

### 🔄 الميزات المنفذة جزئياً

| الميزة | المنجز | المتبقي |
|--------|--------|---------|
| **إدارة العملاء** (ClientController) | CRUD كامل + overview (total/pending/active/closed) + toggle-status | ✅ مكتمل |
| **أطراف القضية** (CaseParty) | النموذج والجدول موجودان + يتم إنشاؤهم عند إنشاء القضية | لا يوجد Controller مخصص لإدارة الأطراف |
| **بذور الصلاحيات** (PermissionSeeder) | يتم إنشاء الصلاحيات وتعيينها للأدوار | يستخدم `guard_name = 'web'` بدلاً من `'api'` (عدم اتساق مع AdminUserSeeder) + خطأ إملائي `advocato` بدلاً من `avocato` |
| **توثيق API** | المسارات معرفة في api.php | لا يوجد توثيق Swagger/OpenAPI |
| **التحقق من صحة البيانات** | يوجد StoreCaseRequest للقضايا | باقي المتحكمات تستخدم تحققاً داخلياً (inline) |

### ❌ الميزات غير المبدوءة

| الميزة | التفاصيل |
|--------|----------|
| **إدارة الأحكام القضائية** (Judgments) | النموذج والجدول موجودان - لا يوجد Controller أو Routes |
| **إدارة المدفوعات** (Payments) | النموذج والجدول موجودان - لا يوجد Controller أو Routes |
| **إدارة المستندات المستقلة** (Documents) | النموذج والجدول موجودان - لا يوجد Controller مخصص |
| **إدارة أطراف القضية** (CaseParty) | لا يوجد API مستقل لإضافة/تعديل/حذف الأطراف |
| **إعادة تعيين كلمة المرور** | غير مطبقة |
| **التحقق من البريد الإلكتروني** | MustVerifyEmail معطل (معلق) في User model |
| **الواجهة الأمامية** (Frontend) | لا توجد واجهة مستخدم - النظام API خالص |
| **بحث/تصفية** | لا توجد نقاط بحث متقدمة للقضايا أو العملاء |
| **تحميل المستندات** (Download) | يمكن رفعها ولكن لا يوجد نقطة تحميل |
| **الاختبارات** | لا توجد اختبارات حقيقية لأي من نقاط API |

---

## 🔐 نظام الصلاحيات (Roles & Permissions)

### الأدوار:
| الدور | المسارات المسموح بها |
|-------|---------------------|
| **Admin** | جميع المسارات (بما فيها إدارة المحامين والعملاء) |
| **Avocato** | القضايا، الجلسات، عرض العملاء، بعض مسارات المحامين |
| **Client** | يمكنه إنشاء قضية جديدة فقط (`POST /api/cases`) |

### الصلاحيات المعرفة (Permissions):
الصلاحيات معرفة ولكنها حالياً غير مفعلة بالكامل - النظام يعتمد على التحقق من **الدور (Role)** مباشرة في Middleware.

### ملاحظات على نظام الصلاحيات:
1. ⚠️ **عدم تناسق الحارس (Guard)**: PermissionSeeder يستخدم `guard_name => 'web'` بينما AdminUserSeeder يستخدم `guard_name => 'api'`
2. ⚠️ **خطأ إملائي**: في PermissionSeeder مكتوب `role('advocato')` والصحيح `avocato`
3. 🔄 المسارات تستخدم `middleware('role:admin')` ولكن بعضها يستخدم `middleware('auth:api')` والبعض الآخر `middleware('auth:sanctum')`

---

## 🌐 جميع نقاط API

### المسارات العامة (بدون مصادقة):
```
POST /api/register       - تسجيل مستخدم جديد
POST /api/login          - تسجيل الدخول
```

### مسارات محمية (auth:sanctum):
```
POST /api/logout         - تسجيل الخروج
GET  /api/me             - جلب بيانات المستخدم الحالي
```

### مسارات محمية (admin فقط):
```
GET    /api/clients                    - قائمة العملاء (id, name, email, total_cases, rate, is_active, created_at, updated_at)
POST   /api/clients                    - إنشاء عميل جديد
GET    /api/clients/{id}               - عرض عميل
PUT    /api/clients/{id}               - تحديث عميل
DELETE /api/clients/{id}               - حذف عميل
GET    /api/clients/{id}/overview      - لوحة إحصائيات العميل (total, pending, active, closed)
PATCH  /api/clients/{id}/toggle-status - تفعيل/تعطيل عميل
POST   /api/clients      - إنشاء عميل (❌ الميثود غير مطبقة)
GET    /api/clients/{id} - عرض عميل
PUT    /api/clients/{id} - تحديث عميل (❌ الميثود غير مطبقة)
DELETE /api/clients/{id} - حذف عميل (❌ الميثود غير مطبقة)
```

### مسارات محمية - إنشاء القضية (للكل حسب دوره):

```
POST   /api/cases                 - إنشاء قضية جديدة (accessible by admin|avocato|client)
                                   📦 هيكل الـ Body يختلف حسب دور المستخدم:
```

🎯 **إذا كان المستخدم Avocato**:
```json
{
  "case_number": "CASE-001",
  "title": "قضية تعويض",
  "client_id": 5,
  "role_in_case": "plaintiff",
  "description": "...",
  "court_name": "محكمة القاهرة"
}
```
> 💡 `client_id` = الطرف الآخر في القضية (العميل)
> يتم تلقائياً: ربط المنشئ (المحامي) في `case_lawyers` + ربط العميل في `case_parties`

🎯 **إذا كان المستخدم Client**:
```json
{
  "case_number": "CASE-001",
  "title": "قضية تعويض",
  "lawyer_id": 3,
  "side": "plaintiff",
  "description": "...",
  "court_name": "محكمة القاهرة"
}
```
> 💡 `lawyer_id` = المحامي المطلوب تولي القضية
> يتم تلقائياً: ربط المنشئ (العميل) في `case_parties` + ربط المحامي في `case_lawyers`

🎯 **إذا كان المستخدم Admin**:
```json
{
  "case_number": "CASE-001",
  "title": "قضية تعويض",
  "parties": [
    { "user_id": 2, "role_in_case": "plaintiff" },
    { "user_id": 3, "role_in_case": "defendant" }
  ],
  "lawyers": [
    { "lawyer_id": 4, "side": "plaintiff" },
    { "lawyer_id": 5, "side": "defendant" }
  ],
  "description": "...",
  "court_name": "محكمة القاهرة"
}
```

### مسارات محمية (admin أو avocato) عبر `auth:api`:
```
GET    /api/cases-overview        - لوحة إحصائيات القضايا (active, closed, pending, suspended, flagged)
GET    /api/cases                 - قائمة القضايا
GET    /api/cases/{id}            - عرض قضية
PUT    /api/cases/{id}            - تحديث قضية
DELETE /api/cases/{id}            - حذف قضية

GET    /api/case-sessions         - قائمة الجلسات
POST   /api/case-sessions         - إنشاء جلسة جديدة (مع رفع ملف)
GET    /api/case-sessions/{id}    - عرض جلسة
PUT    /api/case-sessions/{id}    - تحديث جلسة (مع رفع ملف)
DELETE /api/case-sessions/{id}    - حذف جلسة

GET    /api/lawyers               - قائمة المحامين
POST   /api/lawyers               - إنشاء محامٍ جديد
GET    /api/lawyers/{id}          - عرض محامٍ
PUT    /api/lawyers/{id}          - تحديث محامٍ
DELETE /api/lawyers/{id}          - حذف محامٍ
GET    /api/lawyers/overview      - لوحة إحصائيات المحامي (total, pending, active, closed, suspended, flagged)
PATCH  /api/lawyers/{id}/toggle-status  - تفعيل/تعطيل محامٍ
GET    /api/lawyers/{id}/cases    - قضايا محامٍ معين

GET    /api/legals                - قائمة القوانين
POST   /api/legals                - إنشاء قانون جديد
GET    /api/legals/{id}           - عرض قانون
PUT    /api/legals/{id}           - تحديث قانون
DELETE /api/legals/{id}           - حذف قانون

GET    /api/lawyer-documents             - قائمة ملفات المحامي
POST   /api/lawyer-documents             - رفع ملف/شهادة جديدة 📦 (multipart: title, file, type?, description?)
GET    /api/lawyer-documents/{id}        - عرض ملف
PUT    /api/lawyer-documents/{id}        - تحديث ملف (مع إمكانية تغيير الملف)
DELETE /api/lawyer-documents/{id}        - حذف ملف
GET    /api/lawyer-documents/lawyer/{lawyerId} - كل مستندات محامٍ معين

GET    /api/warning-histories              - قائمة الإنذارات
POST   /api/warning-histories              - إنشاء إنذار جديد (body: lawyer_id, reason)
PATCH  /api/warning-histories/{id}/toggle-status - تغيير حالة الإنذار (body: status)
GET    /api/warning-histories/{id}         - عرض إنذار
PUT    /api/warning-histories/{id}         - تحديث إنذار
DELETE /api/warning-histories/{id}         - حذف إنذار
```

---

## 🧬 أنماط البرمجة المستخدمة (Patterns)

| النمط | مكان الاستخدام |
|-------|---------------|
| **Traits** | `ApiResponse` لتوحيد استجابات JSON، `HandleDocuments` لرفع/إدارة المستندات |
| **API Resource Controllers** | جميع المتحكمات تتبع نمط `apiResource` |
| **Role Middleware** | Spatie `role` middleware مسجل كـ alias في `bootstrap/app.php` |
| **Soft Deletes** | مستخدم في `cases` و `documents` |
| **Database Transactions** | مستخدم في CaseSessionController لضمان سلامة رفع الملفات مع إنشاء الجلسة |
| **API Guard** | يستخدم Sanctum driver |

### ملاحظة هامة:
- ❌ **لا يوجد** Repository Pattern
- ❌ **لا يوجد** Service Layer (كل المنطق التجاري داخل المتحكمات مباشرة)

---

## 🚀 كيفية تشغيل المشروع

### المتطلبات الأساسية:
- PHP ^8.3
- Composer
- SQLite (أو MySQL/PostgreSQL)

### خطوات التشغيل:

```bash
# 1. تثبيت الاعتماديات
composer install

# 2. نسخ ملف البيئة
cp .env.example .env

# 3. إنشاء مفتاح التطبيق
php artisan key:generate

# 4. تشغيل الهجرات مع البذور
php artisan migrate --seed

# 5. تشغيل الخادم المحلي
php artisan serve
```

### المستخدمون الافتراضيون (بعد تشغيل seeders):

| البريد الإلكتروني | كلمة المرور | الدور |
|------------------|------------|-------|
| admin@avocato.com | password | Admin |
| avocato@avocato.com | password | Avocato |
| client@avocato.com | password | Client |

---

## 🗄️ قاعدة البيانات

بشكل افتراضي، المشروع يستخدم **SQLite** (ملف `database/database.sqlite`). يمكنك التبديل إلى MySQL أو PostgreSQL عن طريق تعديل ملف `.env`.

لتشغيل الهجرات:
```bash
php artisan migrate --seed
```

---

## 🧪 التغطية الاختبارية (Testing)

⚠️ **حالياً لا توجد تغطية اختبارية تذكر:**
- يوجد ملفان تجريبيان فقط (ExampleTest)
- لا توجد اختبارات لأي من نقاط API
- `RefreshDatabase` معلق في ملف Pest.php
- **الأولوية القصوى**: كتابة اختبارات API للميزات الحالية

لتشغيل الاختبارات:
```bash
php artisan test
```

---

## 💪 نقاط القوة والضعف

### نقاط القوة ✅
- هيكلة نظيفة ومنظمة وفق معايير Laravel
- قاعدة بيانات متكاملة بعلاقات ومفاتيح أجنبية صحيحة
- تنسيق استجابات API موحد
- نظام صلاحيات جاهز
- Soft Deletes في الجداول الحساسة
- رفع مستندات آمن مع Transactions
- بذور بيانات جاهزة للاختبار

### نقاط الضعف / التحسين المطلوب ⚠️
- **لا توجد اختبارات** لأي وظيفة API
- **ميزات غير مكتملة**: الأحكام، المدفوعات، المستندات المستقلة، أطراف القضية
- **عدم تناسق الحارس**: بعض المسارات تستخدم `auth:sanctum` وأخرى `auth:api`
- **خطأ في PermissionSeeder**: اسم دور خاطئ (`advocato`) وحارس غير متناسق
- **لا يوجد Service Layer**: منطق الأعمال ممتزج مع المتحكمات
- **لا يوجد توثيق API** (مثل Swagger/OpenAPI)
- **لا يوجد واجهة أمامية** (متوقع أن يكون API خالص لتطبيق جوال أو SPA)

---

## 🗺️ خطة التطوير المستقبلية

### تم الإنجاز مؤخراً:
1. ✅ **إدارة القوانين (Legal)** - إنشاء Model + Migration + Seeder + Controller مع CRUD كامل
2. ✅ **رفع ملفات مع إنشاء القضية** - إضافة support لرفع مستندات اختيارية أثناء إنشاء القضية
3. ✅ **ربط المحامين بالقضية** - إصلاح `lawyers/{id}/cases` وإضافة ربط المحامين عبر `case_lawyers` عند إنشاء القضية + Auto-attach لمنشئ القضية
4. ✅ **إنشاء قضية ذكي (Role-Aware)** - Avocato → يرسل `client_id` فقط ويُضاف تلقائياً كمحامي. Client → يرسل `lawyer_id` فقط ويُضاف تلقائياً كطرف. Admin → تحكم كامل بالمصفوفات
5. ✅ **إضافة حقول المحامي** - إضافة `bar_association_number`، `office_location`، `years_of_experience`، `specialty` لجدول users + rate عشوائي عند الإنشاء
6. ✅ **إصلاح LawyerController** - استخدام `paginate` بدل `get`، `validated()` بدل null coalescing، `Rule::unique` للتحديث، Response موحد
7. ✅ **حالات قضايا ثابتة** - 5 حالات (pending, active, suspended, flagged, closed) مع default=pending للقضايا الجديدة
8. ✅ **Lawyer Overview** - endpoint جديد `GET /api/lawyers/overview` يعيد إحصائيات المحامي
9. ✅ **ملفات المحامي (Lawyer Documents)** - Model + Migration + Controller + Endpoints CRUD كامل مع رفع ملفات (شهادات، رخص، ...)
10. ✅ **إنذارات المحامي (Warning History)** - Model + Migration + Controller + Endpoints CRUD كامل مع توليد warning_id تلقائي (WRN-xxxx) ونظام صلاحيات (avocato يرى فقط إنذاراته)
11. ✅ **إكمال ClientController** - إضافة store, update, destroy, toggleStatus, overview + شكل داتا مخصص في index (id, name, email, total_cases, rate, is_active, created_at, updated_at)

### الأولوية القصوى (High Priority):
1. ✅ إصلاح PermissionSeeder (تصحيح الخطأ الإملائي وتوحيد الحارس)
2. ✅ توحيد Middleware المصادقة (كل المسارات تستخدم نفس الحارس)
3. ✅ إكمال ClientController (إضافة store/update/destroy)
4. ✅ كتابة اختبارات API للميزات الحالية
5. ✅ إنشاء Judgments Controller + Routes
6. ✅ إنشاء Payments Controller + Routes

### أولوية متوسطة (Medium Priority):
7. إنشاء Document Controller مستقل
8. ✅ إنشاء API لتعيين المحامين للقضايا (تم ربطه مع create case)
9. إنشاء API مستقل لإدارة أطراف القضية
10. إضافة ميزة البحث والتصفية (Search/Filter)
11. إضافة تحميل المستندات (Download)

### أولوية منخفضة (Low Priority):
12. إعادة تعيين كلمة المرور + التحقق من البريد الإلكتروني
13. إنشاء Service Layer لفصل منطق الأعمال
14. إنشاء Form Requests لجميع المتحكمات
15. توثيق API باستخدام Swagger/OpenAPI

---

## 👨‍💻 المساهمة في التطوير

عند العمل على أي جزء جديد:

1. تأكد من قراءة `README.md` أولاً لفهم حالة المشروع
2. تحقق من `routes/api.php` لتفادي تعارض المسارات
3. اتبع نفس نمط التنسيق الموجود (ApiResponse Trait، التحقق من الصلاحيات)
4. أضف اختبارات API لكل端点 جديد
5. استخدم `php artisan route:list` لمشاهدة جميع المسارات المسجلة

---

**آخر تحديث: يونيو 2026**
