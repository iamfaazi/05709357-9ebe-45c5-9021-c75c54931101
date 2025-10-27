# Assessment Reporting System

A PHP CLI application that generates diagnostic, progress, and feedback reports for student assessments using SOLID principles and streaming JSON parsing with `halaxa/json-machine`.

## 🚀 Quick Setup (Docker)

### Prerequisites
- Docker
- Docker Compose

### Installation
```bash
# 1. Clone repository
git clone <https://github.com/iamfaazi/05709357-9ebe-45c5-9021-c75c54931101>
cd 05709357-9ebe-45c5-9021-c75c54931101

# 2. Build Docker image
docker-compose build

# 3. Install dependencies
docker-compose run --rm composer install

# 4. Add data files to data/ directory
# - data/students.json
# - data/assessments.json
# - data/questions.json
# - data/student-responses.json

# 5. Run application
docker-compose run --rm app
```

### Running the Application
```bash
# Run application
docker-compose run --rm app

# Run tests
docker-compose run --rm test

```

## 📁 Project Structure
```
assessment-reporting/
├── bin/
│   └── console                    # CLI entry point
├── data/                          # JSON data files
│   ├── students.json
│   ├── assessments.json
│   ├── questions.json
│   └── student-responses.json
├── src/
│   ├── Command/                
│   │   ├── GenerateReportCommand.php
│   ├── Infrastructure/            # Low-level components
│   │   └── JsonStreamReader.php   # Streaming JSON parser
│   ├── Repositories/              # Data access layer
│   │   ├── Concerns/              # Interfaces (SOLID)
│   │   │   ├── RepositoryInterface.php
│   │   ├── StudentRepository.php
│   ├── Reports/                   # Business logic
│   │   ├── Concerns
│   │   │   ├── iReport            # Interfaces (SOLID)
│   │   ├── AbstractReportGenerator.php
│   │   ├── DiagnosticReportGenerator.php
│   │   ├── ProgressReportGenerator.php
│   │   └── FeedbackReportGenerator.php
│   └── Factories/
│       └── ReportGeneratorFactory.php
├── tests/                         # PHPUnit tests
│   └── Reports/
│   └── DataLoaderTest.php
├── Dockerfile                     # Docker image definition
├── docker-compose.yml             # Docker services
├── composer.json                  # PHP dependencies
└── phpunit.xml                    # Test configuration
```

## 🎯 Available Reports

1. **Diagnostic Report** - Student performance by strand
2. **Progress Report** - Improvement over time
3. **Feedback Report** - Hints for incorrect answers

## 🔧 Docker Commands
```bash
# Build image
docker-compose build

# Run application
docker-compose run --rm app

# Run tests
docker-compose run --rm test

# Install dependencies
docker-compose run --rm composer install

# Update dependencies
docker-compose run --rm composer update

# Access PHP shell
docker-compose run --rm php bash

# Clean up
docker-compose down --remove-orphans
```

## 📊 Standard JSON Parse vs JSON Streaming

### Standard JSON Parsing (`json_decode`)
```php
// Loads entire file into memory
$data = json_decode(file_get_contents('student-responses.json'), true);

// Memory usage: ~500 MB for 100K records
foreach ($data as $item) {
    process($item);
}
```

**Problems:**
- ❌ Loads entire file into memory
- ❌ Memory usage scales with file size
- ❌ Crashes with large files
- ❌ Slow startup time

### JSON Streaming (`halaxa/json-machine`)
```php
// Streams file, processes one item at a time
$items = Items::fromFile('student-responses.json');

// Memory usage: ~10 MB regardless of file size
foreach ($items as $item) {
    process($item); // Previous items freed from memory
}
```

**Benefits:**
- ✅ Constant memory usage (~10-15 MB)
- ✅ Processes files of any size
- ✅ Fast startup
- ✅ No memory crashes

**When to Use JSON Streaming:**
- Files > 10 MB
- Processing large datasets
- Memory-constrained environments
- Production systems

**Our Implementation:**
```php
    // JsonStreamReader.php uses json-machine
    private function parseWithJsonMachine(): \Generator
    {
        foreach (Items::fromFile($this->filePath) as $item) {
            yield $item;
        }
    }
```

## 💡 Possible Improvements

### 1. Database Integration
```
Current: JSON files
Improvement: MySQL/PostgreSQL for better queries and indexing
```

### 2. Caching Layer
```
Add Redis/Memcached to cache:
- Frequently accessed students
- Question indices
- Generated reports
```

### 3. API Layer
```
Add REST API endpoints:
- GET /api/reports/diagnostic/{studentId}
- GET /api/reports/progress/{studentId}
- GET /api/reports/feedback/{studentId}
```

### 4. Async Processing
```
Use message queues (RabbitMQ/Redis) for:
- Background report generation
- Batch processing
- Email delivery
```

### 5. Report Export
```
Add export formats:
- PDF reports
- CSV data export
- Excel spreadsheets
```


### 7. Monitoring & Logging
```
Add:
- Performance monitoring (DataDog)
- Error tracking (Sentry)
- Structured logging (Monolog)
```

### 8. Storage Security
```
Set:
- Data files should be read-only
  chmod 444 data/*.json

- Data directory should not be writable
  chmod 555 data/

- Ensure proper ownership
  chown www-data:www-data data/
```


## 🏗️ Architecture Highlights

### Design Patterns
- Singleton Pattern - Ensures a class has only one instance
- Repository Pattern - Data access abstraction
- Factory Pattern - Object creation
