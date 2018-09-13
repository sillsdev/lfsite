using System;
using System.Collections;
using System.Collections.Generic;
using System.Linq;
using System.Reflection;
using System.Text;
using JsonApiDotNetCore.Internal;
using JsonApiDotNetCore.Models;
using MongoDB.Driver;
using SIL.XForge.Models;

namespace SIL.XForge.Services
{
    public class SchemaResourceService
    {
        private static readonly Type[] DictionaryInterfaces =
        {
            typeof(IDictionary<,>),
            typeof(IDictionary),
            typeof(IReadOnlyDictionary<,>)
        };

        private readonly Schema _schema;

        public SchemaResourceService(Schema schema)
        {
            _schema = schema;
        }

        public SchemaResource Get()
        {
            var models = new Dictionary<string, SchemaModel>();
            foreach (ContextEntity resourceType in _schema.ResourceTypes)
                models[Singularize(resourceType.EntityName)] = CreateModel(resourceType);
            return new SchemaResource {
                Version = 1,
                Models = models
            };
        }

        private SchemaModel CreateModel(ContextEntity resourceType)
        {
            var attributes = new Dictionary<string, SchemaAttribute>();
            foreach (AttrAttribute attr in resourceType.Attributes)
                attributes[Camelize(attr.PublicAttributeName)] = CreateAttribute(resourceType, attr);

            var relationships = new Dictionary<string, SchemaRelationship>();
            foreach (RelationshipAttribute relationship in resourceType.Relationships)
            {
                if (resourceType.EntityName == "users"
                    && relationship.PublicRelationshipName == Resource.OwnerRelationship)
                {
                    continue;
                }
                if (resourceType.EntityName == "projects"
                    && relationship.PublicRelationshipName == ProjectDataResource.ProjectRelationship)
                {
                    continue;
                }
                relationships[Camelize(relationship.PublicRelationshipName)] = CreateRelationship(resourceType,
                    relationship);
            }

            return new SchemaModel
            {
                Attributes = attributes,
                Relationships = relationships
            };
        }

        private SchemaAttribute CreateAttribute(ContextEntity resourceType, AttrAttribute attr)
        {
            PropertyInfo pi = resourceType.EntityType.GetProperty(attr.InternalAttributeName);
            string type = "object";
            switch (Type.GetTypeCode(pi.PropertyType))
            {
                case TypeCode.String:
                case TypeCode.Char:
                    type = "string";
                    break;
                case TypeCode.Boolean:
                    type = "boolean";
                    break;
                case TypeCode.Byte:
                case TypeCode.SByte:
                case TypeCode.UInt16:
                case TypeCode.UInt32:
                case TypeCode.UInt64:
                case TypeCode.Int16:
                case TypeCode.Int32:
                case TypeCode.Int64:
                case TypeCode.Decimal:
                case TypeCode.Double:
                case TypeCode.Single:
                    type = "number";
                    break;
                case TypeCode.DateTime:
                    type = "date";
                    break;
                default:
                    if (IsEnumerableType(pi.PropertyType) && !IsDictionaryType(pi.PropertyType))
                        type = "array";
                    break;
            }
            return new SchemaAttribute { Type = type };
        }

        private SchemaRelationship CreateRelationship(ContextEntity resourceType, RelationshipAttribute relationship)
        {
            ContextEntity relationshipType;
            if (relationship.Type == typeof(ProjectResource))
                relationshipType = _schema.ContextGraph.GetContextEntity("projects");
            else
                relationshipType = _schema.ContextGraph.GetContextEntity(relationship.Type);

            RelationshipAttribute inverseRelationship = null;
            if  (relationship.PublicRelationshipName != Resource.OwnerRelationship)
            {
                inverseRelationship = relationshipType.Relationships
                    .FirstOrDefault(r => r.PublicRelationshipName != Resource.OwnerRelationship
                        && r.Type.IsAssignableFrom(resourceType.EntityType));
            }

            return new SchemaRelationship
            {
                Type = relationship.IsHasMany ? "hasMany" : "hasOne",
                Model = Singularize(relationshipType.EntityName),
                Inverse = Camelize(inverseRelationship?.PublicRelationshipName)
            };
        }

        private static string Singularize(string word)
        {
            if (word == null)
                return null;

            if (word.EndsWith("s"))
                return word.Substring(0, word.Length - 1);
            return word;
        }

        private static string Camelize(string word)
        {
            if (word == null)
                return null;

            var sb = new StringBuilder();
            foreach (string part in word.Split(' ', '.', '_', '-'))
            {
                string newPart = part.ToLowerInvariant();
                if (sb.Length > 0)
                {
                    sb.Append(newPart.Substring(0, 1).ToUpperInvariant());
                    newPart = newPart.Substring(1);
                }
                sb.Append(newPart);
            }
            return sb.ToString();
        }

        private static bool IsEnumerableType(Type type)
        {
            return typeof(IEnumerable).IsAssignableFrom(type);
        }

        private static bool IsDictionaryType(Type type)
        {
            if (type.IsInterface && IsDictionaryInterfaceType(type))
                return true;
            return type.GetInterfaces().Any(t => IsDictionaryInterfaceType(t));
        }

        private static bool IsDictionaryInterfaceType(Type type)
        {
            return DictionaryInterfaces
                .Any(i => i == type || (type.IsGenericType && i == type.GetGenericTypeDefinition()));
        }
    }
}
